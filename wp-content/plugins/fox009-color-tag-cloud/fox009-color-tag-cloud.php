<?php
/*
Plugin Name: Fox009 Color Tag Cloud
Plugin URI: https://wordpress.org/plugins/fox009-color-tag-cloud/
Description: A beautiful, dynamic and colorful tag cloud.
Version: 1.0.2
Author: Fox009
Author URI: http://www.fox009.cn/
*/

class Fox009_Color_Tag_Cloud_Widget extends WP_Widget {

	private $defaults = array(
		'title'					=> '',
		'style'					=> 'bg_1',
		'font_family'			=> 'open_sans',
		'font_weight'			=> 'normal',
		'smallest'				=> 12,
		'largest'				=> 16,
		'unit'					=> 'px',
		'number'				=> 45,
		'column_count'          => 'none',
		'format'				=> 'flat',
		'separator'				=> '',
		'orderby'				=> 'name',
		'order'					=> 'ASC',
		'exclude'				=> array( '0' => '1' ),
		'include'				=> array( '0' => '0' ),
		'link'					=> 'view',
		'taxonomy'				=> array('post_tag'),
		'post_type'				=> '',
		'show_count'			=> 0,
		'text_transform'		=> 'none',
		'nofollow'				=> 0,
		'background_color'		=> '',
		'gradient_effect'		=> 'none',
		'mouseover_animation'	=> 'updown',
		'text_shadow'			=> 'bottom_right',
		'box_shadow'			=> 'bottom_right',
		'tooltip'				=> 1,
		'hide_empty'			=> 0,
		'childless'				=> 0,
		'no_link'				=> 0,
		'show_more'				=> 0
	);

	public function __construct() {
		$widget_options = array(
			'description'					=> __('A super colorful tag cloud.', 'fox009-color-tag-cloud'),
			'customize_selective_refresh'	=> true,
		);
		parent::__construct('Fox009_Color_Tag_Cloud', __('Fox009 Color Tag Cloud', 'fox009-color-tag-cloud'), $widget_options);
		
		$this->defaults['title'] = __('Tags', 'fox009-color-tag-cloud');
		
		add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
		add_action('wp_enqueue_scripts', array($this, 'scripts'));
		add_action('admin_enqueue_scripts', array($this, 'option_scripts'));
	}
	
	public static function register_widget(){
		register_widget( self::class );
	}
	
	public function load_plugin_textdomain(){
		load_plugin_textdomain('fox009-color-tag-cloud');
	}

	public function scripts() {
		wp_enqueue_script("jquery");
		wp_register_style('fox009-color-tag-cloud', plugins_url('/assets/css/widget.css', __FILE__));
		wp_enqueue_style('fox009-color-tag-cloud');
		wp_enqueue_script('fox009-color-tag-cloud', plugins_url('/assets/js/widget.js', __FILE__));
	}

	public function option_scripts() {
		wp_enqueue_script("jquery");
		wp_register_style('fox009-color-tag-cloud', plugins_url( '/assets/css/admin.css', __FILE__));
		wp_enqueue_style('fox009-color-tag-cloud');
		wp_register_script('fox009-color-tag-cloud', plugins_url( '/assets/js/admin.js', __FILE__));
		wp_enqueue_script('fox009-color-tag-cloud');
	}

	public function widget($args, $instance) {
		include(dirname( __FILE__ ) . '/includes/widget.php');
	}

	public function form($instance) {
		include dirname( __FILE__ ) . '/includes/settings.php';
		include dirname( __FILE__ ) . '/includes/form.php';
	}

	public function update($new_instance, $old_instance) {
		include dirname( __FILE__ ) . '/includes/settings.php';
		include dirname( __FILE__ ) . '/includes/update.php';
		return $instance;
	}
	
	function tag_cloud( $args ) {
		include dirname( __FILE__ ) . '/includes/tag_cloud.php';
		return $return;
	}
	
	private function add_control($id, $args = array()){
		include dirname( __FILE__ ) . '/includes/add_control.php';
	}
}

add_action('widgets_init', array('Fox009_Color_Tag_Cloud_Widget', 'register_widget'));