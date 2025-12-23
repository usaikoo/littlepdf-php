<?php

declare(strict_types=1);

namespace LittlePdf;

final readonly class TextOptions
{
    public function __construct(
        public TextAlign $align = TextAlign::LEFT,
        public ?float $width = null,
        public string $color = '#000000',
    ) {
    }
}

