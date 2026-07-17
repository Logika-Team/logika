<?php
declare(strict_types=1);

function require_contains(string $source, string $needle, string $message): void
{
	if (!str_contains($source, $needle)) {
		fwrite(STDERR, "FAIL: {$message}\n");
		exit(1);
	}
}

$root = dirname(__DIR__);
$scss = (string) file_get_contents($root . '/source/scss/components/cards/_course-card.scss');
$css = (string) file_get_contents($root . '/wordpress/wp-content/themes/logika-theme/assets/css/course-card.css');

require_contains($scss, "&__media {\n        position: relative;\n        height: 258px;", 'SCSS fixes the course-card media height.');
require_contains($scss, 'object-fit: contain;', 'SCSS keeps course illustrations uncropped.');
require_contains($css, '.course-card__media{height:258px;', 'Served CSS fixes the course-card media height.');
require_contains($css, '.course-card__image img{width:100%;height:100%;object-fit:contain}', 'Served CSS contains course illustrations.');

echo "Course card image sizing test passed.\n";
