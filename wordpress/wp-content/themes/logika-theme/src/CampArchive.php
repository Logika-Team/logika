<?php

declare(strict_types=1);

final class Logika_Theme_Camp_Archive {
	public static function render(): void {
		Logika_Theme_Source_Markup::renderPage( 'camps' );
	}
}
