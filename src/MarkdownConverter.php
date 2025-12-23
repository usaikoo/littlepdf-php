<?php

declare(strict_types=1);

namespace LittlePdf;

final class MarkdownConverter
{
    public function __construct(
        private float $width = 612,
        private float $height = 792,
        private float $margin = 72
    ) {
    }

    /**
     * Convert markdown to PDF
     * Supports: # headers, - lists, 1. numbered lists, --- rules, paragraphs with word wrap
     */
    public function convert(string $md): string
    {
        $W = $this->width;
        $H = $this->height;
        $M = $this->margin;
        $doc = new PDFBuilder();
        $textW = $W - $M * 2;
        $bodySize = 11;
        $lineH = $bodySize * 1.5;

        /** @var array{text: string, size: float, indent: int, spaceBefore: float, spaceAfter: float, rule?: bool, color?: string}[] */
        $items = [];

        $wrap = function (string $text, float $size, float $maxW) use ($items): array {
            $words = explode(' ', $text);
            $lines = [];
            $line = '';
            foreach ($words as $word) {
                $test = $line ? $line . ' ' . $word : $word;
                if (PDFBuilder::measureTextStatic($test, $size) <= $maxW) {
                    $line = $test;
                } else {
                    if ($line) {
                        $lines[] = $line;
                    }
                    $line = $word;
                }
            }
            if ($line) {
                $lines[] = $line;
            }
            return $lines ?: [''];
        };

        $prevType = 'start';
        foreach (explode("\n", $md) as $raw) {
            $line = rtrim($raw);
            if (preg_match('/^#{1,3}\s/', $line)) {
                preg_match('/^#+/', $line, $matches);
                $lvl = strlen($matches[0]);
                $size = [22, 16, 13][$lvl - 1];
                $before = $prevType === 'start' ? 0 : [14, 12, 10][$lvl - 1];
                $wrapped = $wrap(substr($line, $lvl + 1), $size, $textW);
                foreach ($wrapped as $i => $l) {
                    $items[] = [
                        'text' => $l,
                        'size' => $size,
                        'indent' => 0,
                        'spaceBefore' => $i === 0 ? $before : 0,
                        'spaceAfter' => 4,
                        'color' => '#111111'
                    ];
                }
                $prevType = 'header';
            } elseif (preg_match('/^[-*]\s/', $line)) {
                $wrapped = $wrap(substr($line, 2), $bodySize, $textW - 18);
                foreach ($wrapped as $i => $l) {
                    $items[] = [
                        'text' => ($i === 0 ? '- ' : '  ') . $l,
                        'size' => $bodySize,
                        'indent' => 12,
                        'spaceBefore' => 0,
                        'spaceAfter' => 2
                    ];
                }
                $prevType = 'list';
            } elseif (preg_match('/^\d+\.\s/', $line, $matches)) {
                $num = $matches[0];
                $text = substr($line, strlen($num));
                $wrapped = $wrap($text, $bodySize, $textW - 18);
                foreach ($wrapped as $i => $l) {
                    $items[] = [
                        'text' => ($i === 0 ? $num . ' ' : '   ') . $l,
                        'size' => $bodySize,
                        'indent' => 12,
                        'spaceBefore' => 0,
                        'spaceAfter' => 2
                    ];
                }
                $prevType = 'list';
            } elseif (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $line)) {
                $items[] = [
                    'text' => '',
                    'size' => $bodySize,
                    'indent' => 0,
                    'spaceBefore' => 8,
                    'spaceAfter' => 8,
                    'rule' => true
                ];
                $prevType = 'rule';
            } elseif (trim($line) === '') {
                if ($prevType !== 'start' && $prevType !== 'blank') {
                    $items[] = [
                        'text' => '',
                        'size' => $bodySize,
                        'indent' => 0,
                        'spaceBefore' => 0,
                        'spaceAfter' => 4
                    ];
                }
                $prevType = 'blank';
            } else {
                $wrapped = $wrap($line, $bodySize, $textW);
                foreach ($wrapped as $l) {
                    $items[] = [
                        'text' => $l,
                        'size' => $bodySize,
                        'indent' => 0,
                        'spaceBefore' => 0,
                        'spaceAfter' => 4,
                        'color' => '#111111'
                    ];
                }
                $prevType = 'para';
            }
        }

        $pages = [];
        $y = $H - $M;
        $pg = [];
        $ys = [];
        foreach ($items as $item) {
            $needed = $item['spaceBefore'] + $item['size'] + $item['spaceAfter'];
            if ($y - $needed < $M) {
                $pages[] = ['items' => $pg, 'ys' => $ys];
                $pg = [];
                $ys = [];
                $y = $H - $M;
            }
            $y -= $item['spaceBefore'];
            $ys[] = $y;
            $pg[] = $item;
            $y -= $item['size'] + $item['spaceAfter'];
        }
        if (count($pg)) {
            $pages[] = ['items' => $pg, 'ys' => $ys];
        }

        foreach ($pages as $page) {
            $doc->page($W, $H, function ($ctx) use ($page, $M, $W) {
                foreach ($page['items'] as $i => $it) {
                    if (isset($it['rule']) && $it['rule']) {
                        $ctx->line($M, $page['ys'][$i], $W - $M, $page['ys'][$i], '#e0e0e0', 0.5);
                    } elseif ($it['text']) {
                        $ctx->text($it['text'], $M + $it['indent'], $page['ys'][$i], $it['size'], new TextOptions(
                            color: $it['color'] ?? '#000000'
                        ));
                    }
                }
            });
        }

        return $doc->build();
    }
}

