<?php

declare(strict_types=1);

final class Logika_Theme_Lead_Form {
	private static int $age_select_index = 0;
	private static int $city_select_index = 0;

	public static function render_age_select( int $page_id = 0 ): string {
		$page_id = $page_id > 0 ? $page_id : (int) get_option( 'page_on_front' );
		$placeholder = trim( (string) get_field( 'home_form_age_placeholder', $page_id ) );
		$placeholder = '' !== $placeholder ? $placeholder : 'Вік дитини (від 7 до 17)';
		$options = self::age_options( $page_id );
		$control_id = 'logika-child-age-' . ++self::$age_select_index;

		$html = '<div class="main-form__select-wrap" data-logika-age-select><select name="child_age" class="main-form__select" aria-label="' . esc_attr( $placeholder ) . '" aria-hidden="true" tabindex="-1" required>';
		$html .= '<option value="">' . esc_html( $placeholder ) . '</option>';

		foreach ( $options as $value => $label ) {
			$html .= '<option value="' . esc_attr( (string) $value ) . '">' . esc_html( $label ) . '</option>';
		}

		$html .= '</select><button class="main-form__input main-form__age-trigger" type="button" aria-haspopup="listbox" aria-expanded="false" aria-controls="' . esc_attr( $control_id ) . '"><span class="main-form__age-label">' . esc_html( $placeholder ) . '</span></button>';
		$html .= '<ul class="main-form__age-dropdown" id="' . esc_attr( $control_id ) . '" role="listbox" hidden>';

		foreach ( $options as $value => $label ) {
			$html .= '<li><button class="main-form__age-option" type="button" role="option" aria-selected="false" data-value="' . esc_attr( (string) $value ) . '">' . esc_html( $label ) . '</button></li>';
		}

		return $html . '</ul></div>';
	}

	public static function render_city_select(): string {
		$control_id = 'logika-city-' . ++self::$city_select_index;

		return '<div class="main-form__city-select" data-logika-city-select><input type="hidden" name="city_id" required><button class="main-form__input main-form__city-trigger" type="button" aria-haspopup="listbox" aria-expanded="false" aria-controls="' . esc_attr( $control_id ) . '"><span class="main-form__city-label">Оберіть місто</span></button><div class="main-form__city-dropdown" id="' . esc_attr( $control_id ) . '" hidden><input class="main-form__city-search" type="search" placeholder="Пошук міста" aria-label="Пошук міста"><ul class="main-form__city-list" role="listbox"></ul><p class="main-form__city-empty" hidden>Місто не знайдено</p></div></div>';
	}

	/**
	 * @return array<int, string>
	 */
	private static function age_options( int $page_id ): array {
		$options = array();

		foreach ( (array) get_field( 'home_form_age_options', $page_id ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$value = absint( $row['value'] ?? 0 );
			$label = trim( (string) ( $row['label'] ?? '' ) );

			if ( $value > 0 && '' !== $label ) {
				$options[ $value ] = $label;
			}
		}

		$normalized = array();
		foreach ( range( 7, 17 ) as $age ) {
			$normalized[ $age ] = $options[ $age ] ?? $age . ' років';
		}

		return $normalized;
	}
}
