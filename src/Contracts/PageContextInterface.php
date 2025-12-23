<?php

declare(strict_types=1);

namespace LittlePdf\Contracts;

use LittlePdf\TextOptions;

interface PageContextInterface
{
    public function text(string $str, float $x, float $y, float $size, ?TextOptions $opts = null): void;

    public function rect(float $x, float $y, float $w, float $h, string $fill): void;

    public function line(float $x1, float $y1, float $x2, float $y2, string $stroke, float $lineWidth = 1.0): void;

    public function image(string $jpegBytes, float $x, float $y, float $w, float $h): void;
}

