<?php

namespace GRIM_SG;

abstract class MediaSitemap extends Sitemap {
	abstract public function add_urls( string $url, array $media ): void;

	abstract public function filter_value( string $value ): bool;

	/**
	 * Add URLS Callback function
	 */
	public function urlsCallback() {
		return 'addMediaUrl';
	}

	public function get_post_media( int $post_id, string $post_type ): array {
		return apply_filters( 'sgg_media_post_urls', array(), $post_id, $post_type );
	}

	/**
	 * Collect Media URLs for Sitemap
	 */
	public function collect_urls() {
		$post_types = array( 'page', 'post' );

		foreach ( $post_types as $key => $post_type ) {
			if ( isset( $this->settings->{$post_type}->media_sitemap ) && ! $this->settings->{$post_type}->media_sitemap ) {
				unset( $post_types[ $key ] );
			}
		}

		if ( sgg_pro_enabled() ) {
			foreach ( $this->get_cpt() as $cpt ) {
				if ( ! empty( $this->settings->{$cpt} ) && ! empty( $this->settings->{$cpt}->media_sitemap ) ) {
					$post_types[] = $cpt;
				}
			}
		}

		$args = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'has_password'   => false,
			'orderby'        => 'post_modified',
			'order'          => 'DESC',
			'posts_per_page' => -1,
		);

		$posts   = new \WP_Query( $args );
		$pattern = '/\[.+?\]/im';

		foreach ( $posts->posts as $post ) {
			$content = $post->post_content;

			if ( ! empty( $content ) && preg_match( $pattern, $content ) ) {
				preg_match_all( $pattern, $content, $shortcode_matches );

				foreach ( $shortcode_matches as $shortcodes ) {
					foreach ( $shortcodes as $shortcode ) {
						$do_shortcode = do_shortcode( $shortcode );
						$content      = str_replace( $shortcode, $do_shortcode, $content );
					}
				}
			}

			$media = $this->get_post_media( $post->ID, $post->post_type );
			$urls  = array();

			if ( preg_match_all( '(https?://[-_.!~*()a-zA-Z0-9;/?:@&=+$%#纊-黑亜-熙ぁ-んァ-ヶ]+)', $content, $result ) !== false ) {
				$urls = array_values( array_unique( $result[0] ) );
			}

			$urls = apply_filters( 'sgg_sitemap_post_media_urls', $urls, $post->ID );

			if ( ! empty( $urls ) ) {
				foreach ( $urls as $url ) {
					if ( $this->filter_value( $url ) ) {
						$media[] = $url;
					}
				}
			}

			if ( ! empty( $media ) ) {
				$this->add_urls( get_permalink( $post ), array_unique( $media ) );
			}
		}
	}
}
