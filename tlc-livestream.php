<?php

/**
 * Plugin Name: TLC Livestream
 * Plugin URI: https://github.com/mikemayer67/tlc-live
 * Description: Manages dynamic embedding of a YouTube livestream using the YouTube API to determine status of live, recorded, and upcoming streams.
 * Version: 0.0.2
 * Author: Michael A. Mayer
 * Requires PHP: 5.3.0
 * License: GPLv3
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */

if( ! defined('WPINC') ) {
  die;
}

define('TLC_LIVESTREAM_DIR',plugin_dir_path(__FILE__));
define('TLC_LIVESTREAM_URL',plugin_dir_url(__FILE__));

require_once 'admin/settings.php';
require_once 'admin/maintenance.php';
require_once 'public/shortcode.php';

require_once __DIR__ . '/vendor/autoload.php';

register_activation_hook(   __FILE__, 'TLC\Live\handle_activate' );
register_deactivation_hook( __FILE__, 'TLC\Live\handle_deactivate');
register_uninstall_hook(    __FILE__, 'TLC\Live\handle_uninstall');

if( is_admin() ) {
  add_action('admin_menu','TLC\Live\Settings\handle_admin_menu');
  add_action('admin_init','TLC\Live\Settings\handle_admin_init');
  add_action('init','TLC\Live\Settings\handle_init');
}

add_shortcode('tlc-livestream','TLC\Live\Public\handle_shortcode');

