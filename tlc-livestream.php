<?php
namespace TLC\Live;

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

if( ! defined('WPINC') ) { die; }

/**
 * scope the specified string to the plugin namespace
 *
 * @param string $name function, variable, class, etc. in plugin namespace
 * @return string namespace scoped name
 */
function ns($s)
{
  return __NAMESPACE__.'\\'.$s;
}

/**
 * return absolute path to a file in the plugin directory
 * 
 * @param path relative to plugin directory
 * @return absolute path to plugin file
 */
function tlc_plugin_path($rel_path)
{
  return plugin_dir_path(__FILE__).'/'.$rel_path;
}

/**
 * return absolute path to the plugin file
 * 
 * @param path relative to plugin directory
 * @return absolute path to plugin file
 */
function tlc_plugin_file()
{
  return __FILE__;
}

require_once tlc_plugin_path('logger.php');
require_once tlc_plugin_path('settings.php');

/**
 * plugin activation hooks
 */

function handle_activate()
{
  log_info('activate: '.__NAMESPACE__);
  Settings::activate();
}

function handle_deactivate()
{
  log_info('deactivate: '.__NAMESPACE__);
}

function handle_uninstall()
{
  Settings::uninstall();
}

register_activation_hook(   tlc_plugin_file(), ns('handle_activate') );
register_deactivation_hook( tlc_plugin_file(), ns('handle_deactivate') );
register_uninstall_hook(    tlc_plugin_file(), ns('handle_uninstall') );


/**
 * admin setup
 */
if( is_admin() )
{
  require_once tlc_plugin_path('admin.php');
}

/**
 * shortcode setup (non-admin)
 */
require_once 'shortcode.php';
add_shortcode('tlc-livestream', ns('handle_shortcode'));

