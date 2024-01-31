<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;

class Tools extends Controller {
	public function __construct() {
		add_action( 'wp', array( $this, 'ping_sitemap' ) );
		add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 100, 3 );
		add_action( 'admin_init', array( $this, 'run_tools_actions' ) );
	}

	public function ping_sitemap() {
		if ( get_transient( self::$slug . '-daily-ping' ) ) {
			return;
		}

		$settings = $this->get_settings();

		if ( $settings->ping_sitemap ) {
			$sitemap = ( new Sitemap() )->generate_sitemap();

			$sitemap->submitSitemap(); // Ping to Search Engines

			update_option( self::$slug . '-last-ping', time() );
		}

		if ( $settings->ping_google_news ) {
			$this->ping_google_news( $settings->google_news_url );
		}

		set_transient( self::$slug . '-daily-ping', time(), DAY_IN_SECONDS );
	}

	public function transition_post_status( $new_status, $old_status, $post ) {
		$settings = $this->get_settings();

		if ( $settings->ping_google_news_post || 'publish' === $old_status || 'publish' !== $new_status ) {
			return;
		}

		$exclude_posts = json_decode( stripslashes( $settings->google_news_exclude ?? '' ) );

		if ( empty( $this->settings->{$post->post_type}->google_news ) || in_array( $post->ID, $exclude_posts, true ) ) {
			return;
		}

		$this->ping_google_news( $settings->google_news_url );
	}

	public function ping_google_news( $sitemap_url = '' ) {
		if ( empty( $sitemap_url ) ) {
			$settings    = $this->get_settings();
			$sitemap_url = $settings->google_news_url;
		}

		wp_remote_request( 'https://www.google.com/ping?sitemap=' . urlencode( trailingslashit( get_bloginfo( 'url' ) ) . $sitemap_url ) );
	}

	public function run_tools_actions() {
		if ( ! isset( $_POST['sgg_tools_nonce'] ) || ! wp_verify_nonce( $_POST['sgg_tools_nonce'], GRIM_SG_BASENAME . '-tools' ) ) {
			return;
		}

		if ( isset( $_POST['sgg-ping-sitemaps'] ) ) {
			$sitemap = ( new Sitemap() )->generate_sitemap();

			$sitemap->submitSitemap();

			$this->add_admin_notice( __( 'Search Engines pinged about your Sitemaps changes.', 'xml-sitemap-generator-for-google' ) );
		} elseif ( isset( $_POST['sgg-ping-google-news'] ) ) {
			$this->ping_google_news();

			$this->add_admin_notice( __( 'Google pinged about your Google News Sitemap changes.', 'xml-sitemap-generator-for-google' ) );
		} elseif ( isset( $_POST['sgg-flush-rewrite-rules'] ) ) {
			Frontend::activate_plugin();

			$this->add_admin_notice( __( 'WordPress Rewrite Rules flushed.', 'xml-sitemap-generator-for-google' ) );
		} elseif ( isset( $_POST['sgg-clear-cache'] ) ) {
			Cache::clear();

			$this->add_admin_notice( __( 'Sitemaps Cache cleared.', 'xml-sitemap-generator-for-google' ) );
		}
	}

	public function add_admin_notice( $message ) {
		add_settings_error( 'tools_admin_notice', 'tools_admin_notice', $message, 'success' );
	}
}
