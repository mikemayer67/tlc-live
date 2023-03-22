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

define('TLC_LIVESTREAM_DIR',plugin_dir_path(__FILE__));

require_once 'admin/settings.php';
require_once 'admin/maintenance.php';

register_activation_hook(   __FILE__, 'TLC\Live\handle_activate' );
register_deactivation_hook( __FILE__, 'TLC\Live\handle_deactivate');
register_uninstall_hook(    __FILE__, 'TLC\Live\handle_uninstall');

add_action('admin_menu',['TLC\Live\Settings','menu']);
#add_action('admin_init','TLC\Live\Settings','init']);

