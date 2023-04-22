<?php
namespace TLC\Live;

/**
 * Setup and handling of the settings page in the admin backend
 */

if( ! defined('WPINC') ) { die; }
if( ! is_admin() ) { return; }

require_once tlc_plugin_path('logger.php');
require_once tlc_plugin_path('settings.php');

const SETTINGS_NONCE = 'tlc-live-settings';

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
  log_info("handle_admin_menu");

  add_options_page(
    'TLC Livestream Settings', // page title
    'TLC Livestream', // menu title
    'manage_options', // required capability
    'tlc-livestream-settings', // settings page slug
    ns('populate_settings_page'), // callback to populate settingsn page
  );
    
}

add_action('admin_menu', ns('handle_admin_menu'));
add_action('admin_init', ns('handle_admin_init'));
add_action('init',       ns('handle_init'));


function populate_settings_page()
{
  log_info("populate_settings_page");

  if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

  if( ($_POST['action'] ?? null) == 'update' )
  {
    log_info("update settings");
    log_info("_POST: ".json_encode($_POST));
  }

  $settings = Settings::instance();
  $api_key = $settings->api_key ?? "";
  $channel = $settings->channel ?? "";
  $playlist = $settings->playlist ?? "";

  $title = esc_html(get_admin_page_title());
  $nonce = wp_nonce_field(SETTINGS_NONCE);
  require tlc_plugin_path('templates/settings_page.htm');
}



