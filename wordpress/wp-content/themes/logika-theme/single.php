<?php

declare(strict_types=1);

get_header();
echo Logika_Theme_Article_Page::render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();
