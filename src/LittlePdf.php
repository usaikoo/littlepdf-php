<?php

declare(strict_types=1);

namespace LittlePdf;

use LittlePdf\Contracts\PDFBuilderInterface;

final class LittlePdf
{
    public static function create(): PDFBuilderInterface
    {
        return new PDFBuilder();
    }

    /**
     * Measure text width in points
     *
     * @param string $str Text to measure
     * @param float $size Font size in points
     * @return float Width in points
     */
    public static function measureText(string $str, float $size): float
    {
        return PDFBuilder::measureTextStatic($str, $size);
    }
}

