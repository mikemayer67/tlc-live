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


function populate_settings_page()
{
  if( !current_user_can('manage_options') ) { wp_die('Unauthorized user'); }

  $cur_tab = $_GET["tab"] ?? 'settings';

  echo "<div class=wrap>";
  require tlc_plugin_path('templates/settings_header.php');

  switch($cur_tab) {
  case "settings":
    populate_settings_tab();
    break;
  case "shortcode":
    populate_shortcode_tab();
    break;
  case "test":
    populate_test_tab();
    break;
  }

  echo "</div>";
}

function populate_settings_tab()
{
  $settings = Settings::instance();
  $updated = false;

  if( ($_POST['action'] ?? null) == 'update' )
  {
    log_info("update settings");
    if( !wp_verify_nonce($_POST['_wpnonce'],SETTINGS_NONCE) ) { 
      log_error("failed to validate nonce");
      wp_die("Bad nonce");
    }
    log_info("_POST: ".json_encode($_POST));

    $api_key = $_POST[API_KEY];
    $settings->set(API_KEY,$api_key);

    $channel = $_POST[CHANNEL_ID];
    $settings->set(CHANNEL_ID,$channel);

    $playlist = $_POST[PLAYLIST_ID];
    $settings->set(PLAYLIST_ID,$playlist);

    $autoplay = isset($_POST[AUTOPLAY]);
    $settings->set(AUTOPLAY,$autoplay);

    $controls = isset($_POST[CONTROLS]);
    $settings->set(CONTROLS,$controls);

    $enablekb = isset($_POST[ENABLE_KB]);
    $settings->set(ENABLE_KB,$enablekb);

    $fullscreen = isset($_POST[FULL_SCREEN]);
    $settings->set(FULL_SCREEN,$fullscreen);

    $modestbranding = isset($_POST[MODEST_BRANDING]);
    $settings->set(MODEST_BRANDING,$modestbranding);

    $transition = (
      60 * ( (int)($_POST['transition_m']) +
      60 * ( (int)($_POST['transition_h']) +
      24 * ( (int)($_POST['transition_d']) ) ) )
    );
    $settings->set(TRANSITION,$transition);

    $query_freq = 60 * (int)($_POST[QUERY_FREQ]);
    if( $query_freq == 0 ) { $query_freq = 300; }
    $settings->set(QUERY_FREQ,$query_freq);

    $updated = true;
  }
  else
  {
    $api_key = $settings->get(API_KEY);
    $channel = $settings->get(CHANNEL_ID);
    $playlist = $settings->get(PLAYLIST_ID);
    $autoplay = $settings->get(AUTOPLAY);
    $controls = $settings->get(CONTROLS);
    $enablekb = $settings->get(ENABLE_KB);
    $fullscreen = $settings->get(FULL_SCREEN);
    $modestbranding = $settings->get(MODEST_BRANDING);
    $transition = $settings->get(TRANSITION);
    $query_freq = $settings->get(QUERY_FREQ);
  }

  // transition frequency is enterred in days, hours, minutes
  $transition = floor($transition/60);
  $transition_m = $transition % 60;
  $transition   = ($transition - $transition_m) / 60;
  $transition_h = $transition % 24;
  $transition_d = ($transition - $transition_h) / 24;
  // query is enterred in minutes
  $query_freq = floor($query_freq/60);

  $nonce = wp_nonce_field(SETTINGS_NONCE);

  require tlc_plugin_path('templates/settings_tab.php');
}

function populate_shortcode_tab()
{
  require tlc_plugin_path('templates/shortcode.php');
}

function populate_test_tab()
{
  echo "add templates/test.php";
}
