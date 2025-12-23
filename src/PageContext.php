<?php

declare(strict_types=1);

namespace LittlePdf;

use LittlePdf\Contracts\PageContextInterface;
use LittlePdf\Internal\Ref;

final class PageContext implements PageContextInterface
{
    /** @var string[] */
    private array $ops;

    /** @var array{name: string, ref: Ref}[] */
    private array $images;

    private int $imageCount;

    public function __construct(
        private PDFBuilder $pdf,
        array &$ops,
        array &$images,
        int &$imageCount
    ) {
        $this->ops = &$ops;
        $this->images = &$images;
        $this->imageCount = &$imageCount;
    }

    public function text(string $str, float $x, float $y, float $size, ?TextOptions $opts = null): void
    {
        $opts = $opts ?? new TextOptions();
        $align = $opts->align->value;
        $boxWidth = $opts->width;
        $color = $opts->color;

        $tx = $x;
        if ($align !== 'left' && $boxWidth !== null) {
            $textWidth = PDFBuilder::measureTextStatic($str, $size);
            if ($align === 'center') {
                $tx = $x + ($boxWidth - $textWidth) / 2;
            }
            if ($align === 'right') {
                $tx = $x + $boxWidth - $textWidth;
            }
        }

        $rgb = PDFBuilder::parseColor($color);
        if ($rgb) {
            $this->ops[] = sprintf("%.3f %.3f %.3f rg", $rgb[0], $rgb[1], $rgb[2]);
        }
        $this->ops[] = 'BT';
        $this->ops[] = "/F1 $size Tf";
        $this->ops[] = sprintf("%.2f %.2f Td", $tx, $y);
        $this->ops[] = $this->pdfString($str) . ' Tj';
        $this->ops[] = 'ET';
    }

    public function rect(float $x, float $y, float $w, float $h, string $fill): void
    {
        $rgb = PDFBuilder::parseColor($fill);
        if ($rgb) {
            $this->ops[] = sprintf("%.3f %.3f %.3f rg", $rgb[0], $rgb[1], $rgb[2]);
            $this->ops[] = sprintf("%.2f %.2f %.2f %.2f re", $x, $y, $w, $h);
            $this->ops[] = 'f';
        }
    }

    public function line(float $x1, float $y1, float $x2, float $y2, string $stroke, float $lineWidth = 1.0): void
    {
        $rgb = PDFBuilder::parseColor($stroke);
        if ($rgb) {
            $this->ops[] = sprintf("%.2f w", $lineWidth);
            $this->ops[] = sprintf("%.3f %.3f %.3f RG", $rgb[0], $rgb[1], $rgb[2]);
            $this->ops[] = sprintf("%.2f %.2f m", $x1, $y1);
            $this->ops[] = sprintf("%.2f %.2f l", $x2, $y2);
            $this->ops[] = 'S';
        }
    }

    public function image(string $jpegBytes, float $x, float $y, float $w, float $h): void
    {
        $imgWidth = 0;
        $imgHeight = 0;
        $len = strlen($jpegBytes);

        for ($i = 0; $i < $len - 1; $i++) {
            if (ord($jpegBytes[$i]) === 0xFF &&
                (ord($jpegBytes[$i + 1]) === 0xC0 || ord($jpegBytes[$i + 1]) === 0xC2)) {
                $imgHeight = (ord($jpegBytes[$i + 5]) << 8) | ord($jpegBytes[$i + 6]);
                $imgWidth = (ord($jpegBytes[$i + 7]) << 8) | ord($jpegBytes[$i + 8]);
                break;
            }
        }

        $imgName = '/Im' . $this->imageCount++;
        $imgRef = $this->pdf->addObject([
            'Type' => '/XObject',
            'Subtype' => '/Image',
            'Width' => $imgWidth,
            'Height' => $imgHeight,
            'ColorSpace' => '/DeviceRGB',
            'BitsPerComponent' => 8,
            'Filter' => '/DCTDecode',
            'Length' => strlen($jpegBytes)
        ], $jpegBytes);

        $this->images[] = ['name' => $imgName, 'ref' => $imgRef];

        $this->ops[] = 'q';
        $this->ops[] = sprintf("%.2f 0 0 %.2f %.2f %.2f cm", $w, $h, $x, $y);
        $this->ops[] = "$imgName Do";
        $this->ops[] = 'Q';
    }

    private function pdfString(string $str): string
    {
        return '(' . str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', '\\r', '\\n'],
            $str
        ) . ')';
    }
}

