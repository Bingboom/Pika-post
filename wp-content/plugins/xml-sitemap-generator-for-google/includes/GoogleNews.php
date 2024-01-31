<?php

namespace GRIM_SG;

class GoogleNews extends Sitemap {
	public static $template = 'google-news';

	private $blog_language = null;

	/**
	 * Add URLS Callback function
	 */
	public function urlsCallback() {
		return 'addNewsUrl';
	}

	/**
	 * Adding Google News Sitemap Headers
	 */
	public function extraSitemapHeader() {
		return array( 'xmlns:news' => 'http://www.google.com/schemas/sitemap-news/0.9' );
	}

	/**
	 * Collect Sitemap URLs
	 */
	public function collect_urls() {
		$this->add_posts();
	}

	/**
	 * Add all Posts to Sitemap
	 */
	public function add_posts() {
		$front_page_id = get_option( 'page_on_front' );
		$post_types    = array( 'page', 'post' );

		foreach ( $post_types as $key => $post_type ) {
			if ( isset( $this->settings->{$post_type}->google_news ) && ! $this->settings->{$post_type}->google_news ) {
				unset( $post_types[ $key ] );
			}
		}

		if ( sgg_pro_enabled() ) {
			foreach ( $this->get_cpt() as $cpt ) {
				if ( ! empty( $this->settings->{$cpt} ) && ! empty( $this->settings->{$cpt}->google_news ) ) {
					$post_types[] = $cpt;
				}
			}
		}

		$args = array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'post__not_in'   => array( $front_page_id ),
			'has_password'   => false,
			'orderby'        => 'post_modified',
			'order'          => 'DESC',
			'posts_per_page' => -1,
		);

		$posts = new \WP_Query( apply_filters( 'sgg_google_news_query_args', $args, $this->settings ) );

		foreach ( $posts->posts as $post ) {
			if ( apply_filters( 'xml_sitemap_include_post', true, $post->ID ) ) {
				$this->add_url(
					get_permalink( $post ),
					$post->ID,
					$post->post_title,
					get_date_from_gmt( $post->post_date_gmt, DATE_W3C ),
					$post->post_type
				);
			}
		}
	}

	/**
	 * Add Google News Sitemap Url
	 *
	 * @param string $url
	 * @param int $id
	 * @param string $title
	 * @param string $last_modified
	 * @param string $post_type
	 */
	public function add_url( $url, $id, $title, $last_modified = '', $post_type = 'post' ) {
		$this->urls[] = array(
			$url, // URL
			! empty( $settings->google_news_name ) ? $settings->google_news_name : get_bloginfo( 'name' ), // Publication Name
			apply_filters( 'xml_sitemap_news_language', $this->get_blog_language(), $id, $post_type ), // Publication Language
			$title, // Title
			$last_modified, // Last Modified
			$id, // ID
		);
	}

	/**
	 * Get Blog Language
	 */
	public function get_blog_language() {
		if ( null === $this->blog_language ) {
			$this->blog_language = sgg_parse_language( get_bloginfo( 'language' ) );
		}

		return $this->blog_language;
	}

	public static function is_older_than_48h( $date ) {
		$now = new \DateTime();
		$now->modify( '-48 hours' );

		$last_modified = new \DateTime( $date );

		return $last_modified < $now;
	}
}
