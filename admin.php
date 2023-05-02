<?php
namespace TLC\Live;

/**
 * Setup and handling of the settings page in the admin backend
 */

if( ! defined('WPINC') ) { die; }
if( ! is_admin() ) { return; }

require_once tlc_plugin_path('include/logger.php');
require_once tlc_plugin_path('settings.php');

const SETTINGS_NONCE = 'tlc-live-settings';
const SETTINGS_PAGE_SLUG = 'tlc-livestream-settings';

function handle_init()
{
  log_info("handle_init");
  wp_enqueue_style('tlc-livestream', tlc_plugin_url('css/tlc-livestream.css'));

  #add_javascript goes here
}

function handle_admin_init()
{
  log_info("handle_admin_init");
}

function handle_admin_menu()
{
  add_options_page(
    'TLC Livestream', // page title
    'TLC Livestream', // menu title
    'manage_options', // required capability
    SETTINGS_PAGE_SLUG, // settings page slug
    ns('populate_settings_page'), // callback to populate settingsn page
  );
    
}

add_action('admin_menu', ns('handle_admin_menu'));
add_action('admin_init', ns('handle_admin_init'));
add_action('init',       ns('handle_init'));


/**
 * Populates the contents of the Settings page on the admin dashboard
 */
function populate_settings_page()
{
  if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

  $cur_tab = $_GET["tab"] ?? 'overview';

  echo "<div class=wrap>";
  require tlc_plugin_path('templates/settings_header.php');
  require tlc_plugin_path('templates/'.$cur_tab.'_tab.php');
  echo "</div>";
}

