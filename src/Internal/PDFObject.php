<?php

declare(strict_types=1);

namespace LittlePdf\Internal;

final class PDFObject
{
    public function __construct(
        public int $id,
        public array $dict,
        public ?string $stream = null
    ) {
    }
}

