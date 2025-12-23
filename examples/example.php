<?php

declare(strict_types=1);

// Use Composer autoloader if available, otherwise use bootstrap
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../bootstrap.php';
}

use LittlePdf\LittlePdf;
use LittlePdf\TextOptions;
use LittlePdf\TextAlign;
use LittlePdf\MarkdownConverter;

// Create a sample PDF
$doc = LittlePdf::create();

// Page 1: Overview and Basic Features
$doc->page(function($p) {
    $margin = 40;
    $pw = 612 - $margin * 2;  // page width minus margins = 532

    // Header
    $p->rect($margin, 716, $pw, 36, '#2563eb');
    $p->text('LITTLE PDF', $margin + 15, 726, 24, new TextOptions(color: '#ffffff'));
    $p->text('Example Document - Page 1', $margin + $pw - 180, 728, 12, new TextOptions(color: '#ffffff'));

    // Title
    $p->text('Welcome to LittlePDF!', $margin, 670, 20);
    $p->text('A minimal PDF generation library', $margin, 650, 12, new TextOptions(color: '#666666'));

    // Content
    $p->text('Features:', $margin, 600, 14);
    
    $features = [
        'Text rendering with Helvetica font',
        'Rectangles and shapes',
        'Lines and borders',
        'JPEG image support',
        'Multiple pages',
        'Markdown to PDF conversion'
    ];

    $y = 575;
    foreach ($features as $feature) {
        $p->text("• $feature", $margin + 20, $y, 11, new TextOptions(color: '#333333'));
        $y -= 25;
    }

    // Text Alignment Examples
    $y -= 20;
    $p->text('Text Alignment:', $margin, $y, 14);
    $y -= 25;
    
    $alignY = $y;
    $p->text('Left', $margin, $alignY, 11, new TextOptions(color: '#333333', align: TextAlign::LEFT));
    $p->text('Center', $margin, $alignY - 20, 11, new TextOptions(color: '#333333', align: TextAlign::CENTER, width: $pw));
    $p->text('Right', $margin, $alignY - 40, 11, new TextOptions(color: '#333333', align: TextAlign::RIGHT, width: $pw));
    $y = $alignY - 60;

    // Text Colors and Sizes
    $y -= 10;
    $p->text('Text Colors & Sizes:', $margin, $y, 14);
    $y -= 25;
    $p->text('Small (8pt)', $margin, $y, 8, new TextOptions(color: '#666666'));
    $y -= 15;
    $p->text('Normal (12pt)', $margin, $y, 12);
    $y -= 18;
    $p->text('Large (18pt)', $margin, $y, 18, new TextOptions(color: '#2563eb'));
    $y -= 25;
    $p->text('Red Text', $margin, $y, 12, new TextOptions(color: '#ef4444'));
    $y -= 18;
    $p->text('Green Text', $margin, $y, 12, new TextOptions(color: '#10b981'));
    $y -= 18;
    $p->text('Purple Text', $margin, $y, 12, new TextOptions(color: '#8b5cf6'));

    // Footer
    $footerY = max(100, $y - 20);
    $p->line($margin, $footerY, $margin + $pw, $footerY, '#e5e7eb', 0.5);
    $p->text('Page 1 of 3', $margin, $footerY - 20, 10, new TextOptions(align: TextAlign::CENTER, width: $pw, color: '#999999'));
});

// Page 2: Shapes, Lines, and Images
$doc->page(function($p) {
    $margin = 40;
    $pw = 612 - $margin * 2;

    // Header
    $p->rect($margin, 716, $pw, 36, '#10b981');
    $p->text('SHAPES & IMAGES', $margin + 15, 726, 24, new TextOptions(color: '#ffffff'));
    $p->text('Page 2', $margin + $pw - 80, 728, 12, new TextOptions(color: '#ffffff'));

    $y = 670;

    // Rectangles Section
    $p->text('Rectangles:', $margin, $y, 14);
    $y -= 25;
    
    // Different colored rectangles
    $p->rect($margin, $y - 30, 100, 30, '#2563eb');
    $p->text('Blue', $margin + 5, $y - 12, 10, new TextOptions(color: '#ffffff'));
    
    $p->rect($margin + 120, $y - 30, 100, 30, '#ef4444');
    $p->text('Red', $margin + 125, $y - 12, 10, new TextOptions(color: '#ffffff'));
    
    $p->rect($margin + 240, $y - 30, 100, 30, '#10b981');
    $p->text('Green', $margin + 245, $y - 12, 10, new TextOptions(color: '#ffffff'));
    
    $p->rect($margin + 360, $y - 30, 100, 30, '#f59e0b');
    $p->text('Orange', $margin + 365, $y - 12, 10, new TextOptions(color: '#ffffff'));
    
    $y -= 50;

    // Lines Section
    $p->text('Lines:', $margin, $y, 14);
    $y -= 25;
    
    // Different line styles
    $p->line($margin, $y, $margin + 200, $y, '#000000', 1);
    $p->text('Thick (1pt)', $margin + 210, $y - 3, 10, new TextOptions(color: '#333333'));
    $y -= 20;
    
    $p->line($margin, $y, $margin + 200, $y, '#2563eb', 0.5);
    $p->text('Medium (0.5pt)', $margin + 210, $y - 3, 10, new TextOptions(color: '#333333'));
    $y -= 20;
    
    $p->line($margin, $y, $margin + 200, $y, '#ef4444', 0.25);
    $p->text('Thin (0.25pt)', $margin + 210, $y - 3, 10, new TextOptions(color: '#333333'));
    $y -= 20;
    
    // Diagonal line
    $p->line($margin, $y, $margin + 150, $y - 50, '#8b5cf6', 1);
    $p->text('Diagonal', $margin + 160, $y - 30, 10, new TextOptions(color: '#333333'));
    $y -= 60;

    // Image Section
    $p->text('JPEG Image Support:', $margin, $y, 14);
    $y -= 25;

    // Load and display signature image if available
    $signaturePath = __DIR__ . '/signature.jpg';
    if (file_exists($signaturePath)) {
        $signatureBytes = file_get_contents($signaturePath);
        $sigWidth = 200;
        $sigHeight = 80;
        $p->text('Signature Example:', $margin, $y, 11, new TextOptions(color: '#333333'));
        $y -= 15;
        $p->image($signatureBytes, $margin, $y - $sigHeight, $sigWidth, $sigHeight);
        $y -= $sigHeight + 20;
    } else {
        $p->text('(Signature image not found)', $margin, $y, 10, new TextOptions(color: '#999999'));
        $y -= 20;
    }

    // Combined Example
    $y -= 20;
    $p->text('Combined Example:', $margin, $y, 14);
    $y -= 25;
    
    // Box with border and text
    $boxY = $y - 40;
    $p->rect($margin, $boxY, 300, 40, '#f3f4f6');
    $p->line($margin, $boxY, $margin + 300, $boxY, '#000000', 0.5);
    $p->line($margin, $boxY - 40, $margin + 300, $boxY - 40, '#000000', 0.5);
    $p->line($margin, $boxY, $margin, $boxY - 40, '#000000', 0.5);
    $p->line($margin + 300, $boxY, $margin + 300, $boxY - 40, '#000000', 0.5);
    $p->text('Bordered Box with Text', $margin + 10, $boxY - 25, 12);

    // Footer
    $footerY = max(100, $boxY - 60);
    $p->line($margin, $footerY, $margin + $pw, $footerY, '#e5e7eb', 0.5);
    $p->text('Page 2 of 3', $margin, $footerY - 20, 10, new TextOptions(align: TextAlign::CENTER, width: $pw, color: '#999999'));
});

// Page 3: Markdown Example
$doc->page(function($p) {
    $margin = 40;
    $pw = 612 - $margin * 2;

    // Header
    $p->rect($margin, 716, $pw, 36, '#8b5cf6');
    $p->text('MARKDOWN CONVERSION', $margin + 15, 726, 24, new TextOptions(color: '#ffffff'));
    $p->text('Page 3', $margin + $pw - 80, 728, 12, new TextOptions(color: '#ffffff'));

    $p->text('Markdown to PDF:', $margin, 670, 14);
    $p->text('See markdown-example.pdf for full markdown conversion demo', $margin, 650, 11, new TextOptions(color: '#666666'));
    
    // Show markdown example
    $markdownExample = "# Markdown Support\n\nLittlePDF supports converting markdown to PDF with:\n\n## Features\n- Headers (H1, H2, H3)\n- Bullet lists\n- Numbered lists\n- Horizontal rules\n- Automatic word wrapping\n- Multi-page support\n\n### Example List\n1. First item\n2. Second item\n3. Third item\n\n---\n\n**Bold text** and *italic text* (rendered as plain text)";

    $p->text('Sample Markdown:', $margin, 620, 12);
    $y = 600;
    $lines = array_slice(explode("\n", $markdownExample), 0, 15);
    foreach ($lines as $line) {
        if (trim($line)) {
            $size = strpos($line, '#') === 0 ? 12 : 10;
            $color = strpos($line, '#') === 0 ? '#000000' : '#333333';
            $text = preg_replace('/^#+\s*/', '', preg_replace('/\*\*/', '', preg_replace('/\*/', '', $line)));
            $p->text($text, $margin + 20, $y, $size, new TextOptions(color: $color));
        }
        $y -= 15;
    }

    // Footer
    $footerY = 100;
    $p->line($margin, $footerY, $margin + $pw, $footerY, '#e5e7eb', 0.5);
    $p->text('Page 3 of 3', $margin, $footerY - 20, 10, new TextOptions(align: TextAlign::CENTER, width: $pw, color: '#999999'));
});

// Build and save main example
$bytes = $doc->build();
file_put_contents(__DIR__ . '/example.pdf', $bytes);

echo "✅ Created example.pdf\n";
echo "📄 File size: " . strlen($bytes) . " bytes\n";
echo "📑 Pages: 3\n";

// Create separate markdown example
$markdownContent = "# LittlePDF Markdown Example\n\nThis is a demonstration of the markdown to PDF conversion feature.\n\n## Features\n\nLittlePDF supports:\n\n- **Headers** - Multiple levels (H1, H2, H3)\n- **Lists** - Both bullet and numbered\n- **Rules** - Horizontal dividers\n- **Word Wrapping** - Automatic text wrapping\n- **Pagination** - Multi-page documents\n\n### Bullet List Example\n\n- First item\n- Second item\n- Third item with longer text that will wrap automatically\n\n### Numbered List Example\n\n1. First numbered item\n2. Second numbered item\n3. Third numbered item\n\n---\n\n## More Content\n\nThis demonstrates how markdown content is automatically converted to a well-formatted PDF document.\n\nThe library handles:\n- Text alignment\n- Spacing\n- Multiple pages\n- Clean formatting\n\n---\n\n**End of Document**\n";

$converter = new MarkdownConverter();
$markdownBytes = $converter->convert($markdownContent);
file_put_contents(__DIR__ . '/markdown-example.pdf', $markdownBytes);

echo "✅ Created markdown-example.pdf\n";
echo "📄 Markdown PDF size: " . strlen($markdownBytes) . " bytes\n";

// Test measureText
echo "\n📏 measureText test:\n";
echo "\"Hello\" at 12pt = " . number_format(LittlePdf::measureText('Hello', 12), 2) . "pt\n";
echo "\"Hello World\" at 24pt = " . number_format(LittlePdf::measureText('Hello World', 24), 2) . "pt\n";
