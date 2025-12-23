<?php

declare(strict_types=1);

namespace LittlePdf\Contracts;

interface PDFBuilderInterface
{
    /**
     * @param float|callable $widthOrFn
     * @param float|null $height
     * @param callable|null $fn
     */
    public function page(float|callable $widthOrFn, ?float $height = null, ?callable $fn = null): void;

    public function build(): string;

    public function measureText(string $str, float $size): float;
}

