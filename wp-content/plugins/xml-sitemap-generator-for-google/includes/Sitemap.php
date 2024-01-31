<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;
use GRIM_SG\Vendor\QueryBuilder;
use GRIM_SG\Vendor\SitemapGenerator;

class Sitemap extends Controller {
	public static $template = 'sitemap';

	protected $urls = array();

	protected $settings;

	public function __construct() {
		$this->settings = $this->get_settings();
	}

	/**
	 * Generate Sitemap
	 */
	public function show_sitemap( $template, $is_xml = true, $inner_sitemap = null ) {
		if ( sgg_is_sitemap_index( $template ) && ! empty( $inner_sitemap ) ) {
			$template = 'inner-sitemap';
		}

		$sitemap = $this->generate_sitemap( $template, $is_xml );

		try {
			$sitemap->outputSitemap( $template, $is_xml, $inner_sitemap );
		} catch ( \Exception $exc ) {
			echo $exc->getTraceAsString();
		}
	}

	/**
	 * Generate Sitemap
	 */
	public function generate_sitemap( $template = 'sitemap', $is_xml = true ) {
		remove_all_filters( 'pre_get_posts' );

		$sitemap = new SitemapGenerator( sgg_get_home_url() );

		if ( $this->settings->enable_cache ) {
			$cache = new Cache( $template );
			$urls  = $cache->get();

			if ( $urls ) {
				$this->urls = $urls;
			} else {
				$this->collect_urls();
				$cache->set( $this->urls );
			}
		} else {
			$this->collect_urls();
		}

		$sitemap->addUrls( $this->urls, $this->urlsCallback(), $template );

		try {
			$sitemap->createSitemap( $template, $this->extraSitemapHeader(), $is_xml );
		} catch ( \Exception $exc ) {
			echo $exc->getTraceAsString();
		}

		return $sitemap;
	}

	/**
	 * Generate Sitemap XSL Template
	 */
	public static function generate_sitemap_xsl( $template = 'sitemap' ) {
		ob_get_clean();

		header( 'Content-Type: text/xsl; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );

		ob_start();

		global $wp_version;

		if ( version_compare( $wp_version, '5.5.0', '<' ) ) {
			set_query_var( 'args', array( 'template' => $template ) );
		}

		load_template(
			GRIM_SG_PATH . '/templates/xsl/sitemap.php',
			false,
			compact( 'template' )
		);

		ob_end_flush();
	}

	/**
	 * Get Sitemap Table by Template
	 */
	public static function get_sitemap_table( $template = 'sitemap' ) {
		switch ( $template ) {
			case GoogleNews::$template:
				$table = 'google-news';
				break;
			case ImageSitemap::$template:
				$table = 'image-sitemap';
				break;
			case VideoSitemap::$template:
				$table = 'video-sitemap';
				break;
			case 'sitemap-index':
				$table = 'sitemap-index';
				break;
			default:
				$table = 'sitemap';
				break;
		}

		load_template( GRIM_SG_PATH . "/templates/xsl/tables/{$table}.php", false );
	}

	/**
	 * Get Sitemap Title by Template
	 */
	public static function get_sitemap_title( $template ) {
		$title = __( 'Sitemap', 'xml-sitemap-generator-for-google' );

		switch ( $template ) {
			case \GRIM_SG\GoogleNews::$template:
				$title = __( 'Google News', 'xml-sitemap-generator-for-google' );
				break;
			case \GRIM_SG\ImageSitemap::$template:
				$title = __( 'Image Sitemap', 'xml-sitemap-generator-for-google' );
				break;
			case \GRIM_SG\VideoSitemap::$template:
				$title = __( 'Video Sitemap', 'xml-sitemap-generator-for-google' );
				break;
			case 'inner-sitemap':
				$title = __( 'Sitemap', 'xml-sitemap-generator-for-google' );
				break;
			case 'sitemap-index':
				$title = __( 'Sitemap Index', 'xml-sitemap-generator-for-google' );
				break;
		}

		return $title;
	}

	/**
	 * Add URLS Callback function
	 */
	public function urlsCallback() {
		return 'addUrl';
	}

	/**
	 * Adding Google News Sitemap Headers
	 */
	public function extraSitemapHeader() {
		return array();
	}

	/**
	 * Collect Sitemap URLs
	 */
	public function collect_urls() {
		$this->add_home();
		$this->add_posts();
		$this->add_categories();
		$this->add_authors();
		$this->add_archives();
		$this->add_additional_pages();
	}

	/**
	 * Add Home Page to Sitemap
	 */
	public function add_home() {
		$home          = $this->settings->home;
		$front_page_id = get_option( 'page_on_front' );
		$last_modified = ( $front_page_id ) ? get_post_modified_time( 'c', false, $front_page_id ) : gmdate( 'c' );

		if ( $home->include ) {
			$this->add_url(
				sgg_get_home_url(),
				$home->priority,
				$home->frequency,
				$last_modified,
				'page'
			);
		}
	}

	/**
	 * Add all Posts to Sitemap
	 */
	public function add_posts() {
		$front_page_id     = get_option( 'page_on_front' );
		$post_types        = array( 'page', 'post' );
		$priority_provider = $this->get_posts_priority_provider();

		foreach ( $post_types as $key => $post_type ) {
			if ( isset( $this->settings->{$post_type}->include ) && ! $this->settings->{$post_type}->include ) {
				unset( $post_types[ $key ] );
			}
		}

		foreach ( $this->get_cpt() as $cpt ) {
			if ( ! empty( $this->settings->{$cpt} ) && ! empty( $this->settings->{$cpt}->include ) ) {
				$post_types[] = $cpt;
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

		if ( ! empty( $front_page_id ) ) {
			$args['post__not_in'] = array( $front_page_id );
		}

		$posts = new \WP_Query( apply_filters( 'sgg_sitemap_query_args', $args, $this->settings ) );

		foreach ( $posts->posts as $post ) {
			if ( apply_filters( 'xml_sitemap_include_post', true, $post->ID ) ) {
				$this->add_url(
					get_permalink( $post ),
					( null !== $priority_provider && 'post' === $post->post_type )
						? apply_filters( 'sitemap_post_priority', $priority_provider->get_post_priority( $post->comment_count ), $post )
						: apply_filters( 'sitemap_post_priority', $this->settings->{$post->post_type}->priority, $post ),
					apply_filters( 'sitemap_post_frequency', $this->settings->{$post->post_type}->frequency, $post ),
					get_post_modified_time( 'c', false, $post ),
					$post->post_type
				);
			}
		}
	}

	/**
	 * Posts Priority
	 */
	public function get_posts_priority_provider() {
		$class_name = str_replace( '/', '\\', $this->settings->posts_priority ?? '' );

		return class_exists( $class_name ) ? new $class_name( $this->get_comments_count(), $this->get_posts_count() ) : null;
	}

	public function get_comments_count() {
		global $wpdb;

		$cache_key      = self::$slug . '_comments_count';
		$comments_count = wp_cache_get( $cache_key, self::$slug );

		if ( false === $comments_count ) {
			$comments_count = $wpdb->get_var( "SELECT COUNT(*) as `comments_count` FROM {$wpdb->comments} WHERE `comment_approved`='1'" );
			wp_cache_set( $cache_key, $comments_count, self::$slug, 20 );
		}

		return $comments_count;
	}

	public function get_posts_count() {
		global $wpdb;

		$cache_key   = self::$slug . '_posts_count';
		$posts_count = wp_cache_get( $cache_key, self::$slug );

		if ( false === $posts_count ) {
			$posts_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} p WHERE p.post_password = '' AND p.post_type = 'post' AND p.post_status = 'publish' ");
			wp_cache_set( $cache_key, $posts_count, self::$slug, 20 );
		}

		return $posts_count;
	}

	/**
	 * Add all Categories & Tags
	 */
	public function add_categories() {
		$taxonomy_types = array();

		foreach ( $this->get_taxonomy_types() as $taxonomy_type ) {
			if ( ! empty( $this->settings->{$taxonomy_type} ) ) {
				if ( $this->settings->{$taxonomy_type}->include ) {
					$taxonomy_types[] = $taxonomy_type;
				}
			} else {
				if ( $this->settings->category->include ) {
					$taxonomy_types[] = $taxonomy_type;
				}
			}
		}

		if ( ! empty( $taxonomy_types ) ) {
			$args = array(
				'taxonomy'   => $taxonomy_types,
				'hide_empty' => false,
				'number'     => false,
				'fields'     => 'all',
			);

			$terms = get_terms( apply_filters( 'sgg_sitemap_taxonomy_args', $args, $this->settings ) );

			foreach ( $terms as $term ) {
				if ( is_callable( '\WPSEO_Taxonomy_Meta::get_term_meta' ) ) {
					$noindex = \WPSEO_Taxonomy_Meta::get_term_meta( $term->term_id, $term->taxonomy, 'noindex' );
					if ( 'noindex' === $noindex ) {
						continue;
					}
				}

				$args = array(
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'tax_query'      => array(
						array(
							'taxonomy' => $term->taxonomy,
							'field'    => 'id',
							'terms'    => array( $term->term_id ),
						),
					),
				);

				$latest_posts = new \WP_Query( $args );
				$latest_post  = $latest_posts->posts;

				$option = ( ! empty( $this->settings->{$term->taxonomy} ) ) ? $term->taxonomy : 'category';

				if ( ! empty( $latest_post[0] ) ) {
					$this->add_url(
						get_category_link( $term ),
						apply_filters( 'sitemap_term_priority', $this->settings->{$option}->priority, $term ),
						apply_filters( 'sitemap_term_frequency', $this->settings->{$option}->frequency, $term ),
						get_post_modified_time( 'c', false, $latest_post[0] ),
						'category'
					);
				}
			}
		}
	}

	/**
	 * Add all Authors
	 */
	public function add_authors() {
		if ( $this->settings->authors->include ) {
			$args = array(
				'orderby' => 'post_count',
				'order'   => 'DESC',
			);

			$authors_query = new \WP_User_Query( $args );
			$authors       = $authors_query->get_results();

			if ( ! empty( $authors ) ) {
				foreach ( $authors as $author ) {
					$args = array(
						'author'      => $author->ID,
						'orderby'     => 'date',
						'order'       => 'DESC',
						'numberposts' => 1,
					);

					$latest_posts  = get_posts( $args );
					$modified_time = ( ! empty( $latest_posts[0] ) ) ?
						get_post_modified_time( 'c', false, $latest_posts[0] ) :
						gmdate( 'c', strtotime( $author->user_registered ) );

					$this->add_url(
						get_author_posts_url( $author->ID ),
						$this->settings->authors->priority,
						$this->settings->authors->frequency,
						$modified_time,
						'author'
					);
				}
			}
		}
	}

	/**
	 * Add all Archives
	 */
	public function add_archives() {
		global $wpdb;

		$sql = sprintf(
			'SELECT DISTINCT YEAR(post_date) AS year, MONTH(post_date) AS month,
				UNIX_TIMESTAMP(MAX(posts.post_modified)) AS modified_time FROM %s as posts
				WHERE post_status = "publish" AND post_type = "post" AND posts.post_password = ""
				GROUP BY YEAR(post_date), MONTH(post_date)',
			$wpdb->posts
		);

		$archives = QueryBuilder::run_query( $sql );

		foreach ( $archives as $archive ) {
			$option = ( gmdate( 'n' ) === $archive->month && gmdate( 'Y' ) === $archive->year ) ? 'archive' : 'archive_older';
			if ( $this->settings->{$option}->include ) {
				$this->add_url(
					get_month_link( $archive->year, $archive->month ),
					$this->settings->{$option}->priority,
					$this->settings->{$option}->frequency,
					gmdate( 'c', $archive->modified_time ),
					'archive'
				);
			}
		}
	}

	/**
	 * Add Additional Pages
	 */
	public function add_additional_pages() {
		$pages = $this->settings->additional_pages;

		foreach ( $pages as $page ) {
			$last_modified = ! empty( $page['lastmod'] )
				? gmdate( 'c', strtotime( $page['lastmod'] ) )
				: gmdate( 'c' );

			$this->add_url(
				$page['url'],
				$page['priority'],
				$page['frequency'],
				$last_modified,
				'additional'
			);
		}
	}

	/**
	 * Add Sitemap Url
	 *
	 * @param $url
	 * @param $priority
	 * @param $frequency
	 * @param string $last_modified
	 */
	public function add_url( $url, $priority, $frequency, $last_modified = '', $inner_sitemap = '' ) {
		$item = array(
			$url, // URL
			$last_modified, // Last Modified
			$frequency, // Frequency
			number_format( floatval( $priority / 10 ), 1, '.', '' ), // Priority
		);

		if ( sgg_is_sitemap_index( 'sitemap', $this->settings ) ) {
			$this->urls[ $inner_sitemap ][] = $item;
		} else {
			$this->urls[] = $item;
		}
	}
}
