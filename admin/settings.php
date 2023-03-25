<?php
namespace TLC\Live\Settings;

require_once TLC_LIVESTREAM_DIR.'/utils/logger.php';
use function \TLC\Live\log_info;

const SETTINGS_PAGE   = 'tlc-livestream-settings-page';
const YOUTUBE_SECTION = 'tlc-livestream-youtube-section';
const TIMING_SECTION  = 'tlc-livestream-timing-section';

const YOUTUBE_API_KEY = 'tlc_livestream_youtube_api_key';
const YOUTUBE_CLIENT_ID = 'tlc_livestream_youtube_client_id';
const YOUTUBE_CLIENT_SECRET = 'tlc_livestream_youtube_client_secret';
const TIMING_SWITCH = 'tlc_livestream_swith_to_upcoming';

const AJAX_HANDLE = 'tlc_livestream_test';

function handle_init()
{
  log_info("Settings::handle_init ");
  wp_register_script(
    'tlc_livestream_test_script',
    TLC_LIVESTREAM_URL.'/js/livestream_test.js',
    array('jquery')
  );

  wp_localize_script(
    'tlc_livestream_test_script',
    'myAjax',
    [
      'ajaxurl' => admin_url('admin-ajax.php')
    ],
  );

  wp_enqueue_script('jquery');
  wp_enqueue_script('tlc_livestream_test_script');
}

function handle_admin_init()
{
  log_info("Settings::handle_admin_init ");
  add_settings_section(
    YOUTUBE_SECTION,
    'YouTube',
    '',
    SETTINGS_PAGE,
    );

  add_settings_section(
    TIMING_SECTION,
    'Timing',
    [__NAMESPACE__.'\\Populate','timing_section'],
    SETTINGS_PAGE,
    );

  register_and_add_setting('API Key',      YOUTUBE_API_KEY,      YOUTUBE_SECTION);
  register_and_add_setting('Client ID',    YOUTUBE_CLIENT_ID,    YOUTUBE_SECTION);
  register_and_add_setting('Client Secret',YOUTUBE_CLIENT_SECRET,YOUTUBE_SECTION);

  register_and_add_setting('Switch to Upcoming Livestream', TIMING_SWITCH, TIMING_SECTION);

  add_action('wp_ajax_'.AJAX_HANDLE,[__NAMESPACE__.'\\Populate','update_test_result']);
}

function handle_admin_menu()
{
  log_info("Settings::handle_admin_menu ");
  add_options_page(
    'TLC Livestream Settings',
    'TLC Livestream',
    'manage_options',
    SETTINGS_PAGE,
    [__NAMESPACE__.'\\Populate','settings_page'],
  );
}

function register_and_add_setting($label,$setting,$section)
{
  register_setting(
    SETTINGS_PAGE,
    $setting,
    array(
      'type' => 'string',
      'sanitize_callback' => 'sanitize_text_field',
      'default' => '',
    )
  );
  add_settings_field(
    $setting,
    $label,
    [__NAMESPACE__.'\\Populate',$setting],
    SETTINGS_PAGE,
    $section,
  );
}


class Populate
{
  public static function __callStatic($name,$arguments)
  {
    if($name == TIMING_SWITCH) {
      $pattern = "pattern='\s*(\d+d)?\s*(\d+h)?\s*(\d+m)?\s*(\d+s)?\s*'";
    } else {
      $pattern = "";
    }
    $value = esc_attr(get_option($name,""));
    echo "<input type='text' name=$name class='regular-text' value='$value' $pattern>";
  }

  public static function timing_section()
  {
    echo "<div style='font-weight:bold;'>";
    echo "  Enter the time to switch to upcoming livestream using the format '#d #h #m #s'.";
    echo "</div><div styel='padding-left:1em;'>";
    echo "  - You do not need to specify all elements, but they must be specified in the order shown.";
    echo "</div><div styel='padding-left:1em;'>";
    echo "  - If left blank, the switch will occur at the scheduled start time.";
    echo "</div>";
}

  public static function settings_page()
  {
    $title = esc_html(get_admin_page_title());
    echo "<div class='wrap'>";
    echo "<h1>$title</h1>";
    echo "<form action='options.php' method='post'>";
    settings_fields(SETTINGS_PAGE);
    do_settings_sections(SETTINGS_PAGE);
    submit_button('Save Settings');
    echo "</form></div>";

    $ajax_runs = get_option('tlc_livestream_ajax_test',0);
    echo "<b>You have run the AJAX test <span id=ajax_run_count>$ajax_runs</span> times.</b>";
    $action = AJAX_HANDLE;
    $nonce = wp_create_nonce(AJAX_HANDLE);
    $link = admin_url("admin-ajax.php?action=$action&nonce=$nonce");
    echo "<div><a class='ajax_count' data-nonce='$nonce' href='$link'>run AJAX test</a></div>";
  }

  public static function update_test_result()
  {
    log_info("Update_test_result: ".json_encode($_REQUEST));

    if(!wp_verify_nonce($_REQUEST['nonce'],AJAX_HANDLE)) {
      log_info("Bad nonce");
      exit("Bad nonce");
    }

    $cur_count = get_option('tlc_livestream_ajax_test',0);
    $new_count = $cur_count + 1;
    if(update_option('tlc_livestream_ajax_test',$new_count)) {
      $result = [
        'type' => 'success',
        'count' => $new_count,
      ];
    } else {
      $result = [
        'type' => 'error',
        'count' => $cur_count,
      ];
    }

    $key = 'HTTP_X_REQUESTED_WITH';
    $http_x_request_with = array_key_exists($key,$_SERVER) ? $_SERVER[$key] : "";
    log_info("http_x_request_with: $http_x_request_with");
    if(strtolower($http_x_request_with) == 'xmlhttprequest') {
      echo json_encode($result);
    } else {
      header("Location: ".$_SERVER['HTTP_REFERER']);
    }

    die();
  }
}

