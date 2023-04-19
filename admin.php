<?php
namespace TLC\Live;

/**
 * Setup and handling of the settings page in the admin backend
 */

if( ! defined('WPINC') ) { die; }
if( ! is_admin() ) { return; }

require_once tlc_plugin_path('logger.php');
require_once tlc_plugin_path('settings.php');

function handle_init()
{
  log_info("handle_init");
}

function handle_admin_init()
{
  log_info("handle_admin_init");
}

function handle_admin_menu()
{
  log_info("handle_admin_menu");
}


add_action('admin_menu', ns('handle_admin_menu'));
add_action('admin_init', ns('handle_admin_init'));
add_action('init',       ns('handle_init'));

