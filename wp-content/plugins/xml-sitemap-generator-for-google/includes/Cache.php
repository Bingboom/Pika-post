<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;

class Cache extends Controller {
	private static $prefix = 'sgg_cache_';

	public static $sitemaps = array(
		'sitemap',
		'google-news',
		'image-sitemap',
		'video-sitemap',
	);

	public $sitemap;

	public function __construct( $sitemap = 'sitemap' ) {
		$this->sitemap = $sitemap;
	}

	public function set( $urls ) {
		$expiration = self::get_expiration( $this->get_settings() );
		set_transient( self::$prefix . $this->sitemap, $urls, $expiration );
		set_transient( self::$prefix . $this->sitemap . '_time', time(), $expiration );
	}

	public function get() {
		return get_transient( self::$prefix . $this->sitemap );
	}

	public static function get_time( $sitemap ) {
		return get_transient( self::$prefix . $sitemap . '_time' );
	}

	public static function get_time_formatted( $sitemap ) {
		$time = self::get_time( $sitemap );

		return $time
			// translators: %s is Cached Time
			? sprintf( __( '%s ago', 'xml-sitemap-generator-for-google' ), human_time_diff( $time, time() ) )
			: __( 'No Cache', 'xml-sitemap-generator-for-google' );
	}

	public static function delete( $sitemap ): void {
		delete_transient( self::$prefix . $sitemap );
		delete_transient( self::$prefix . $sitemap . '_time' );
	}

	public static function clear(): void {
		foreach ( self::$sitemaps as $sitemap ) {
			self::delete( $sitemap );
		}
	}

	public static function maybe_clear( $expiration ): void {
		foreach ( self::$sitemaps as $sitemap ) {
			if ( $expiration < time() - self::get_time( $sitemap ) ) {
				self::delete( $sitemap );
			}
		}
	}

	public static function get_expiration( $settings ) {
		return intval( $settings->cache_timeout ?? 24 ) * intval( $settings->cache_timeout_period ?? 3600 );
	}
}
