<?php

namespace GRIM_SG\Vendor;

use GRIM_SG\Settings;

class Controller {
	public static $slug = 'xml-sitemap-generator-for-google';

	public function get_settings() {
		$settings      = new Settings();
		$saved_options = get_option( self::$slug );

		if ( ! empty( $saved_options ) ) {
			foreach ( $settings as $key => &$option ) {
				if ( isset( $saved_options->{$key} ) ) {
					$option = $saved_options->{$key};
				}
			}
		}

		return $settings;
	}

	/**
	 * Get Custom Post Types
	 * @return string[]|\WP_Post_Type[]
	 */
	public function get_cpt( $output = 'names' ) {
		$args = array(
			'public'   => true,
			'_builtin' => false,
		);

		return get_post_types( $args, $output );
	}

	/**
	 * Get Taxonomy Types
	 * @return string[]|\WP_Taxonomy[]
	 */
	public function get_taxonomy_types( $output = 'names' ) {
		$args = array(
			'public'  => true,
			'show_ui' => true,
		);

		return get_taxonomies( $args, $output );
	}
}
