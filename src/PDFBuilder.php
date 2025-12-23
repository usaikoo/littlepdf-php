<?php

declare(strict_types=1);

namespace LittlePdf;

use LittlePdf\Contracts\PDFBuilderInterface;
use LittlePdf\Contracts\PageContextInterface;
use LittlePdf\Internal\PDFObject;
use LittlePdf\Internal\Ref;
use LittlePdf\Internal\Serializer;

final class PDFBuilder implements PDFBuilderInterface
{
    // Helvetica widths, ASCII 32-126, units per 1000
    private const WIDTHS = [
        278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278,
        556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556,
        1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778,
        667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 278, 278, 278, 469, 556,
        333, 556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556,
        556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584
    ];

    /** @var PDFObject[] */
    private array $objects = [];

    /** @var Ref[] */
    private array $pages = [];

    private int $nextId = 1;
    private Serializer $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer();
    }

    /**
     * Measure text width in points
     */
    public function measureText(string $str, float $size): float
    {
        $width = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $code = ord($str[$i]);
            $w = ($code >= 32 && $code <= 126) ? self::WIDTHS[$code - 32] : 556;
            $width += $w;
        }
        return ($width * $size) / 1000;
    }

    /**
     * Static helper for measuring text width
     */
    public static function measureTextStatic(string $str, float $size): float
    {
        $width = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $code = ord($str[$i]);
            $w = ($code >= 32 && $code <= 126) ? self::WIDTHS[$code - 32] : 556;
            $width += $w;
        }
        return ($width * $size) / 1000;
    }

    /**
     * Parse hex color to RGB floats
     */
    public static function parseColor(?string $hex): ?array
    {
        if (!$hex || $hex === 'none') {
            return null;
        }
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        return [$r, $g, $b];
    }

    /**
     * Add object to PDF
     * @internal
     */
    public function addObject(array $dict, ?string $streamBytes = null): Ref
    {
        $id = $this->nextId++;
        $this->objects[] = new PDFObject($id, $dict, $streamBytes);
        return new Ref($id);
    }

    public function page(float|callable $widthOrFn, ?float $height = null, ?callable $fn = null): void
    {
        if (is_callable($widthOrFn)) {
            $width = 612.0;
            $height = 792.0;
            $fn = $widthOrFn;
        } else {
            $width = $widthOrFn;
            $height = $height ?? 792.0;
            $fn = $fn;
        }

        $ops = [];
        $images = [];
        $imageCount = 0;

        $ctx = new PageContext($this, $ops, $images, $imageCount);

        $fn($ctx);

        $content = implode("\n", $ops);
        $contentBytes = $content;
        $contentRef = $this->addObject(['Length' => strlen($contentBytes)], $contentBytes);

        $xobjects = [];
        foreach ($images as $img) {
            $xobjects[substr($img['name'], 1)] = $img['ref'];
        }

        $resources = [
            'Font' => ['F1' => null]
        ];
        if (!empty($xobjects)) {
            $resources['XObject'] = $xobjects;
        }

        $pageRef = $this->addObject([
            'Type' => '/Page',
            'Parent' => null,
            'MediaBox' => [0, 0, $width, $height],
            'Contents' => $contentRef,
            'Resources' => $resources
        ]);

        $this->pages[] = $pageRef;
    }

    public function build(): string
    {
        $fontRef = $this->addObject([
            'Type' => '/Font',
            'Subtype' => '/Type1',
            'BaseFont' => '/Helvetica'
        ]);

        $pagesRef = $this->addObject([
            'Type' => '/Pages',
            'Kids' => $this->pages,
            'Count' => count($this->pages)
        ]);

        foreach ($this->objects as $obj) {
            if (isset($obj->dict['Type']) && $obj->dict['Type'] === '/Page') {
                $obj->dict['Parent'] = $pagesRef;
                if (isset($obj->dict['Resources']['Font'])) {
                    $obj->dict['Resources']['Font']['F1'] = $fontRef;
                }
            }
        }

        $catalogRef = $this->addObject([
            'Type' => '/Catalog',
            'Pages' => $pagesRef
        ]);

        $parts = [];
        $offsets = [];

        $parts[] = "%PDF-1.4\n%\xFF\xFF\xFF\xFF\n";

        foreach ($this->objects as $obj) {
            $offsets[$obj->id] = array_sum(array_map(function($p) {
                return is_string($p) ? strlen($p) : strlen($p);
            }, $parts));

            $content = $obj->id . " 0 obj\n" . $this->serializer->serialize($obj->dict) . "\n";
            if ($obj->stream !== null) {
                $content .= "stream\n";
                $parts[] = $content;
                $parts[] = $obj->stream;
                $parts[] = "\nendstream\nendobj\n";
            } else {
                $content .= "endobj\n";
                $parts[] = $content;
            }
        }

        $xrefOffset = array_sum(array_map(function($p) {
            return is_string($p) ? strlen($p) : strlen($p);
        }, $parts));

        $xref = "xref\n0 " . (count($this->objects) + 1) . "\n";
        $xref .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($this->objects); $i++) {
            $xref .= str_pad((string)$offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $parts[] = $xref;

        $parts[] = "trailer\n" . $this->serializer->serialize(['Size' => count($this->objects) + 1, 'Root' => $catalogRef]) . "\n";
        $parts[] = "startxref\n$xrefOffset\n%%EOF\n";

        return implode('', $parts);
    }
}

