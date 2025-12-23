# 📄 littlepdf-php

[![Latest Version](https://img.shields.io/packagist/v/usaikoo/littlepdf-php.svg?style=flat-square)](https://packagist.org/packages/usaikoo/littlepdf-php)
[![Total Downloads](https://img.shields.io/packagist/dt/usaikoo/littlepdf-php.svg?style=flat-square)](https://packagist.org/packages/usaikoo/littlepdf-php)
[![License](https://img.shields.io/packagist/l/usaikoo/littlepdf-php.svg?style=flat-square)](https://packagist.org/packages/usaikoo/littlepdf-php)

> **Minimal PDF creation library for PHP** — ~700 lines of code, zero dependencies, makes real PDFs.

Port of [littlepdf](https://github.com/usaikoo/littlepdf) from TypeScript to PHP.

---

## 🎯 Philosophy

**Ultra-lightweight by design.** We stripped away everything non-essential: TTF fonts, PNG/SVG support, HTML-to-PDF conversion, forms, encryption, and compression. What remains is the core 95% use case: **putting text and images on a page.**

Perfect for building: invoices, receipts, reports, shipping labels, tickets, certificates, contracts, and data exports.

---

## ✨ What's Included

- ✅ **Text** — Helvetica font, any size, hex colors, left/center/right alignment
- ✅ **Shapes** — Rectangles and lines
- ✅ **Images** — JPEG support (photos, logos, signatures)
- ✅ **Pages** — Multiple pages with custom sizes
- ✅ **Markdown** — Convert markdown to PDF with headers, lists, and rules

## ❌ What's Not Included

Custom fonts, PNG/GIF/SVG, vector graphics, forms, encryption, compression, HTML-to-PDF

> 💡 Need those features? Check out [FPDF](https://github.com/Setasign/FPDF) or [TCPDF](https://github.com/tecnickcom/TCPDF).

---

## 📦 Installation

```bash
composer require usaikoo/littlepdf-php
```

---

## 🚀 Getting Started

### Basic Example

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use LittlePdf\LittlePdf;
use LittlePdf\TextOptions;
use LittlePdf\TextAlign;

$doc = LittlePdf::create();

$doc->page(function($ctx) {
    $ctx->rect(50, 700, 200, 40, '#2563eb');           // blue rectangle
    $ctx->text('Hello PDF!', 60, 712, 24, new TextOptions(color: '#ffffff'));
    $ctx->line(50, 680, 250, 680, '#000000', 1);       // black line
});

file_put_contents('output.pdf', $doc->build());
```

### Working with Images

```php
$doc = LittlePdf::create();

$doc->page(function($ctx) {
    $logo = file_get_contents('logo.jpg');
    $ctx->image($logo, 50, 700, 100, 50);
});

file_put_contents('output.pdf', $doc->build());
```

### Text Measurement

```php
use LittlePdf\LittlePdf;

LittlePdf::measureText('Hello', 12); // => 27.34 (points)
```

### Markdown Conversion

```php
use LittlePdf\MarkdownConverter;

$markdown = <<<MD
# Hello World

A minimal PDF from markdown.

## Features
- Headers (h1, h2, h3)
- Bullet lists
- Numbered lists
- Horizontal rules

---

Automatic word wrapping and pagination included.
MD;

$converter = new MarkdownConverter();
$pdf = $converter->convert($markdown);
file_put_contents('output.pdf', $pdf);
```

---

## 📚 API Reference

### LittlePdf

Main factory class for creating PDF documents.

#### Methods

```php
static function create(): PDFBuilderInterface
```
Create a new PDF builder

```php
static function measureText(string $str, float $size): float
```
Measure text width in points

### PDFBuilderInterface

#### Methods

```php
function page(float|callable $widthOrFn, ?float $height = null, ?callable $fn = null): void
```
Add a page to the document

```php
function build(): string
```
Build and return the final PDF content

```php
function measureText(string $str, float $size): float
```
Measure text width in points

### PageContextInterface

Context provided to page callbacks for drawing operations.

#### Methods

```php
function text(string $str, float $x, float $y, float $size, ?TextOptions $opts = null): void
```
Render text at specified position

```php
function rect(float $x, float $y, float $w, float $h, string $fill): void
```
Draw a filled rectangle

```php
function line(float $x1, float $y1, float $x2, float $y2, string $stroke, float $lineWidth = 1.0): void
```
Draw a line

```php
function image(string $jpegBytes, float $x, float $y, float $w, float $h): void
```
Render a JPEG image

### TextOptions

Value object for text rendering options.

#### Constructor

```php
new TextOptions(
    TextAlign $align = TextAlign::LEFT,
    ?float $width = null,
    string $color = '#000000'
)
```

#### Properties

```php
public TextAlign $align
```
Text alignment (LEFT, CENTER, RIGHT)

```php
public ?float $width
```
Width constraint for alignment

```php
public string $color
```
Text color in hex format (e.g., '#ff0000')

### MarkdownConverter

Convert markdown documents to PDF.

#### Constructor

```php
new MarkdownConverter(
    float $width = 612,
    float $height = 792,
    float $margin = 72
)
```

#### Methods

```php
function convert(string $markdown): string
```
Convert markdown to PDF content

## Supported Markdown Features

- Headers (H1-H3): `#`, `##`, `###`
- Unordered lists: `-` or `*`
- Ordered lists: `1.`, `2.`, etc.
- Horizontal rules: `---`, `***`, `___`
- Paragraphs with automatic word wrapping
- Blank lines for spacing

## Coordinate System

PDF uses a coordinate system where:
- Origin (0, 0) is at the bottom-left corner
- X increases to the right
- Y increases upward
- Units are in points (1/72 inch)

Common page sizes:
- US Letter: 612 x 792 points
- A4: 595 x 842 points

---

## 🛠️ Development

### Requirements

- PHP 8.1 or higher
- Composer

### Installation

```bash
composer install
```

### Run Example

```bash
php examples/example.php
```

This generates `example.pdf` and `markdown-example.pdf` in the `examples/` directory.

### Static Analysis

```bash
composer test:types
```

---

## 📄 License

MIT

## Credits

This is a PHP port of [littlepdf](https://github.com/usaikoo/littlepdf) by Sai Ko.
# littlepdf-php
