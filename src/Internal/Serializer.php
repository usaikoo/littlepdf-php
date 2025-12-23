<?php

declare(strict_types=1);

namespace LittlePdf\Internal;

use LittlePdf\Internal\Ref;

final class Serializer
{
    public function serialize(mixed $val): string
    {
        if ($val === null) {
            return 'null';
        }
        if (is_bool($val)) {
            return $val ? 'true' : 'false';
        }
        if (is_int($val)) {
            return (string)$val;
        }
        if (is_float($val)) {
            $str = number_format($val, 4, '.', '');
            return rtrim(rtrim($str, '0'), '.');
        }
        if (is_string($val)) {
            if (str_starts_with($val, '/')) {
                return $val;  // name
            }
            if (str_starts_with($val, '(')) {
                return $val;  // already escaped string
            }
            return $this->pdfString($val);
        }
        if (is_array($val) && isset($val[0])) {
            // Array
            return '[' . implode(' ', array_map([$this, 'serialize'], $val)) . ']';
        }
        if ($val instanceof Ref) {
            return $val->id . ' 0 R';
        }
        if (is_array($val)) {
            // Dictionary
            $pairs = [];
            foreach ($val as $k => $v) {
                if ($v !== null && $v !== '') {
                    $pairs[] = '/' . $k . ' ' . $this->serialize($v);
                }
            }
            return "<<\n" . implode("\n", $pairs) . "\n>>";
        }
        return (string)$val;
    }

    public function pdfString(string $str): string
    {
        return '(' . str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', '\\r', '\\n'],
            $str
        ) . ')';
    }
}

