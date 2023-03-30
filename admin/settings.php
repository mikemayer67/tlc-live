<?php
namespace TLC\Live;

require_once TLC_LIVESTREAM_DIR.'/include/logger.php';
require_once TLC_LIVESTREAM_DIR.'/include/javascript.php';
require_once TLC_LIVESTREAM_DIR.'/include/timing.php';
require_once TLC_LIVESTREAM_DIR.'/include/style.php';

const SETTINGS_PAGE   = 'tlc-livestream-settings-page';
const YOUTUBE_SECTION = 'tlc-livestream-youtube-section';
const TIMING_SECTION  = 'tlc-livestream-timing-section';

const YOUTUBE_API_KEY = 'tlc_livestream_youtube_api_key';
const YOUTUBE_CLIENT_ID = 'tlc_livestream_youtube_client_id';
const YOUTUBE_CLIENT_SECRET = 'tlc_livestream_youtube_client_secret';

const TIMING_SWITCH = 'tlc_livestream_swith_to_upcoming';
const TIMING_POLL_START = 'tlc_livestream_poll_start';
const TIMING_POLL_FREQ = 'tlc_livestream_poll_freq';

const DEFAULT_TIMING_SWITCH = '30m';
const DEFAULT_TIMING_POLL_START = '5m';
const DEFAULT_TIMING_POLL_FREQ = '10s';

const TEST_ACTION = 'tlc_livestream_test';

const AJAX_HANDLE = 'tlc-livestream-test';

function fill_default_settings()
{
  update_option(TIMING_SWITCH,    get_option(TIMING_SWITCH,    DEFAULT_TIMING_SWITCH));
  update_option(TIMING_POLL_START,get_option(TIMING_POLL_START,DEFAULT_TIMING_POLL_START));
  update_option(TIMING_POLL_FREQ, get_option(TIMING_POLL_FREQ, DEFAULT_TIMING_POLL_FREQ));
}

function clear_all_settings()
{
  delete_option(YOUTUBE_API_KEY);
  delete_option(YOUTUBE_CLIENT_ID);
  delete_option(YOUTUBE_CLIENT_SECRET);
  delete_option(TIMING_SWITCH);
  delete_option(TIMING_POLL_START);
  delete_option(TIMING_POLL_FREQ);
}

function handle_init()
{
  log_info("Settings::handle_init ");

  add_javascript('tlc-livestream-test');
  add_css('tlc-livestream');
}

function handle_admin_init()
{
  log_info("Settings::handle_admin_init ");

  setup_section(YOUTUBE_SECTION, 'YouTube API Keys');
  setup_section(TIMING_SECTION,  'Livestream Display');

  register_and_add_setting('API Key',      YOUTUBE_API_KEY,      YOUTUBE_SECTION);
  register_and_add_setting('Client ID',    YOUTUBE_CLIENT_ID,    YOUTUBE_SECTION);
  register_and_add_setting('Client Secret',YOUTUBE_CLIENT_SECRET,YOUTUBE_SECTION);

  register_and_add_setting('Switch to Upcoming', TIMING_SWITCH, TIMING_SECTION);
  register_and_add_setting('Start Polling', TIMING_POLL_START, TIMING_SECTION);
  register_and_add_setting('Polling Frequency', TIMING_POLL_FREQ, TIMING_SECTION);

  add_action('admin_post_'.TEST_ACTION, [__NAMESPACE__.'\\Populate','handle_connection_test']);
  #@@@TODO: Remove the following
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

function setup_section($section, $label)
{
  log_info("setup_section($page,$section,$label)");
  add_settings_section(
    $section,
    $label,
    [__NAMESPACE__.'\\Populate',$section],
    SETTINGS_PAGE,
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
    $info="";
    $format="";
    $extra="";

    switch($name) {
    case TIMING_SECTION:
      echo "<div class='tlc section-info'>";
      echo "This section handles all things related to the timing of the livestream display";
      echo "</div>";
      break;

    case YOUTUBE_SECTION:
      echo "<div class='tlc section-info'>";
      echo "You will need to get the following info from the ";
      echo "<a href='https://console.cloud.google.com'>Google Cloud console</a>.";
      echo "</div>";
      break;

    case TIMING_SWITCH:
      self::text_input(
        $name,
        "How long before scheduled start to show upcoming livestream",
        "\s*(\d+d)?\s*(\d+h)?\s*(\d+m)?\s*(\d+s)?\s*",
        "[#d][#h][#m][#s]",
      );
      break;

    case TIMING_POLL_START:
      self::text_input(
        $name,
        "When to start actively watching for livestream to go live",
        "\s*(\d+m)?\s*(\d+s)?\s*",
        "[#m][#s]",
      );
      break;

    case TIMING_POLL_FREQ:
      self::text_input(
        $name,
        "How often to check for livestream to go live",
        "\s*(\d+m)?\s*(\d+s)?\s*",
        "[#m][#s]",
      );
      break;

    case YOUTUBE_API_KEY:
    case YOUTUBE_CLIENT_ID:
    case YOUTUBE_CLIENT_SECRET:
      self::text_input($name);
      break;
    }
  }


  public static function text_input($name,$info="",$pattern="",$hint="")
  {
    $value = esc_attr(get_option($name,""));
    if($pattern == "") {
      echo "<input type='text' name=$name class='regular-text' value='$value'>";
    } else {
      echo "<input type='text' name=$name class='regular-text' value='$value' pattern='$pattern'>";
    }

    if($hint != '') {
      echo "<span class='tlc setting-format'>$hint</span>";
    }

    if( $info != '' ) {
      echo "<div class='tlc setting-info'>$info</div>";
    }
  }


  public static function settings_page()
  {
    $title = esc_html(get_admin_page_title());
    echo "<div class='wrap'>";
    echo "<h1>$title</h1>";

    # Test Form`
    # @@@TODO: Figure out how to pass back test result: $_REQUEST?
    self::settings_test();

    # Settingsn Form
    echo "<form action='options.php' method='post'>";
    settings_fields(SETTINGS_PAGE);
    do_settings_sections(SETTINGS_PAGE);
    submit_button('Save Settings');
    echo "</form>";

    # @@@TODO : Remove the following
    $timing = json_encode(timing_settings());
    echo "<div>$timing</div>";

    # @@@TODO : Remove the following
    echo "<hr>";
    echo "<div style='margin-left:100px;'>";
    $ajax_runs = get_option('tlc_livestream_ajax_test',0);
    echo "<b>You have run the AJAX test <span id=ajax_run_count>$ajax_runs</span> times.</b>";
    $action = AJAX_HANDLE;
    $nonce = wp_create_nonce(AJAX_HANDLE);
    $link = admin_url("admin-ajax.php?action=$action&nonce=$nonce");
    echo "<div><a class='ajax_count' data-nonce='$nonce' href='$link'>run AJAX test</a></div>";
    echo "</div>";
  }

  public static function settings_test()
  {
    #$link = TLC_LIVESTREAM_URL."/admin/test_connection.php";
    $link = esc_url(admin_url('admin-post.php'));
    $nonce = wp_create_nonce(TEST_ACTION);
    $action = TEST_ACTION;

    echo "<form action='$link' method='post'>";
    echo "<input type='hidden' name='nonce' value='$nonce'>";
    echo "<input type='hidden' name='action' value='$action'>";
    echo "<table class='form-table' role='presentation'>";
    echo "<tr><th scope='row'>Test Connection</th>";
    echo "<td>";
    echo "<input type='submit' name='trigger' class='button' value='Test Livestream  Connection'>";
    echo "<div class='tlc setting-info'>";
    echo "this will count against your daily YouTube API quota";
    echo "</div";
    echo "</td></tr></table>";
    echo "</form>";
  }

  public static function handle_connection_test()
  {
    log_info("handle_connection_test: ".json_encode($_REQUEST));
    $api_key = get_option(YOUTUBE_API_KEY,"");
    $client_id = get_option(YOUTUBE_CLIENT_ID,"");
    $client_secret = get_option(YOUTUBE_CLIENT_SECRET,"");
    $referer_url = $_SERVER['HTTP_REFERER'];

    echo "<div style='font-family:sans-serif;'>";
    echo "<h1>TLC Livestream Plugin</h1>";
    echo "<h2>Connection Test</h2>";
    echo "<table>";
    echo "<tr><th>API Key</th><td>$api_key</td></tr>";
    echo "<tr><th>Client ID</th><td>$client_id</td></tr>";
    echo "<tr><th>Client Secret</th><td>$client_secret</td></tr>";
    echo "<tr><th></th><td>";
    echo "<div style='font-size:large;font-weight:bold;padding-top:30px'>";
    echo "<a href='$referer_url'>Return to settings page</a></h2>";
    echo "</div></td>";
    echo "</tr></table>";
    echo "</div>";
    
    #$test_schedule = [['stream1'=>'test1'],['stream2'=>'test2']];
    #update_option('tlc_livestream_connection_status',True);
    #update_option('tlc_livestream_connection_error','just a test error');
    #update_option('tlc_livestream_stream_schedule',json_encode($test_schedule));
    #$url = $_SERVER['HTTP_REFERER'];
    #wp_redirect($url);
    #exit;
    #("Location: $url");
  }

  # @@@TODO delete this
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

    if(array_key_exists("has_js",$_REQUEST)) {
      log_info("Javascript: ".json_encode($result));
      echo json_encode($result);
    } else {
      $url = $_SERVER['HTTP_REFERER'];
      $url = preg_replace("/&test_count=\d+/","",$url);
      $url = $url.'&test_count='.$result['count'];
      log_info("No Javascript: $url");
      header("Location: $url");
    }

    die();
  }
}

