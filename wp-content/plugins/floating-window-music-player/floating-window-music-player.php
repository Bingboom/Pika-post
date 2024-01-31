<?php
/**
 * Plugin Name: Floating Window Music-Player
 * Plugin URI:  http://eric.cn.com/?p=1187
 * Text Domain: floating-window-music-player
 * Domain Path: /languages
 * Version:     3.4.2
 * Author:      Eric
 * Author URI:  http://eric.cn.com/
 * Description: The plugin supports Netease, Tencent, Xiami, Kugou and Baidu Music online playing on a floating window. 
 */

define('Fwm_Player_URL', plugins_url('', __FILE__));
define('Fwm_Player_VER', '3.4.2');
define('FwmTD', 'floating-window-music-player');
require dirname(__FILE__). '/inc/option.php';
require dirname(__FILE__). '/inc/API.php';
register_activation_hook(__FILE__, 'Fwm_Player_plugin_activate');
function Fwm_Player_plugin_activate() {add_option('Fwm_Player_do_activation_redirect', 1);}
function Fwm_Player_plugin_redirect() {
  if (get_option('Fwm_Player_do_activation_redirect', 0)) {
    delete_option('Fwm_Player_do_activation_redirect');
    wp_redirect(admin_url('admin.php?page=Fwm_Player_options'));
  }
}
function Fwm_load_textdomain() {load_plugin_textdomain(FwmTD, 0, dirname(plugin_basename(__FILE__)). '/languages' );}
function Fwm_addPluginLinks($links, $file) {
  $file == plugin_basename(__FILE__) && array_unshift($links, '<a href="options-general.php?page=Fwm_Player_options">'. __('Setting',FwmTD). '</a>');
  return $links;
}
add_action('init', 'Fwm_load_textdomain');
add_action('admin_init', 'Fwm_Player_plugin_redirect');
add_action('wp_footer', 'Fwm_Player_footer');
add_filter('plugin_action_links', 'Fwm_addPluginLinks', 10, 2);