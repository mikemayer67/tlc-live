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

require_once 'utils/logger.php';
require_once 'admin/settings.php';

function do_activate()
{
  TLC\log_info("activate");
  TLC\log_warning("activate");
  TLC\log_error("activate");
}

function do_deactivate()
{
  TLC\log_info("deactivate");
  TLC\log_warning("deactivate");
  TLC\log_error("deactivate");
}

function do_uninstall()
{
}

register_activation_hook(   __FILE__, 'do_activate' );
register_deactivation_hook( __FILE__, 'do_deactivate');
register_uninstall_hook(    __FILE__, 'do_uninstall');

add_action('admin_menu',['TLC\Livestream\Admin\Settings','menu']);
#add_action('admin_init','TLC\Livestream\Admin\settings_init');


