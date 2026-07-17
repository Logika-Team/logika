<?php

declare(strict_types=1);

$header = file_get_contents( __DIR__ . '/../wordpress/wp-content/themes/logika-theme/source-pages/header.php' );

if ( ! str_contains( (string) $header, 'class="header__login btn" href="https://student.logikaschool.com.ua/login"' ) || ! str_contains( (string) $header, '<span>Увійти</span>' ) ) {
	throw new RuntimeException( 'Header does not contain the student login link.' );
}

echo "Header login link is present.\n";
