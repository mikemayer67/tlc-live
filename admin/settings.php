<?php
namespace TLC\Live;

require_once TLC_LIVESTREAM_DIR.'/include/connection.php';
require_once TLC_LIVESTREAM_DIR.'/include/javascript.php';
require_once TLC_LIVESTREAM_DIR.'/include/logger.php';
require_once TLC_LIVESTREAM_DIR.'/include/style.php';
require_once TLC_LIVESTREAM_DIR.'/include/timing.php';

const SETTINGS_PAGE   = 'tlc-live-settings-page';
const YOUTUBE_SECTION = 'tlc-live-youtube-section';
const TIMING_SECTION  = 'tlc-live-timing-section';

const AJAX_HANDLE = 'tlc-live-test';

function fill_default_settings()
{
  fill_connection_defaults();
  fill_timing_defaults();
}

function clear_all_settings()
{
  clear_connection_settings();
  clear_timing_settings();
}

function handle_init()
{
  log_info("Settings::handle_init ");
  add_css('tlc-livestream');

  # TODO: delete this
  add_javascript('tlc-live-test');
}

function handle_admin_init()
{
  log_info("Settings::handle_admin_init ");

  setup_section(YOUTUBE_SECTION, 'YouTube API Keys');
  setup_section(TIMING_SECTION,  'Livestream Updates');

  register_and_add_setting('API Key',      YOUTUBE_API_KEY,      YOUTUBE_SECTION);
  register_and_add_setting('Client ID',    YOUTUBE_CLIENT_ID,    YOUTUBE_SECTION);
  register_and_add_setting('Client Secret',YOUTUBE_CLIENT_SECRET,YOUTUBE_SECTION);

  register_and_add_setting('Refresh Schedule', SCHEDULE_UPDATE, TIMING_SECTION);
  register_and_add_setting('Switch to Upcoming', TIMING_SWITCH, TIMING_SECTION);
  register_and_add_setting('Start Polling', TIMING_POLL_START, TIMING_SECTION);
  register_and_add_setting('Polling Frequency', TIMING_POLL_FREQ, TIMING_SECTION);

  add_action('admin_post_'.CONNECTION_TEST, ns('handle_connection_test'));

  # TODO: delete this
  add_action('wp_ajax_'.AJAX_HANDLE,[ns('Populate'),'update_test_result']);
}

function handle_admin_menu()
{
  log_info("Settings::handle_admin_menu ");
  add_options_page(
    'TLC Livestream Settings',
    'TLC Livestream',
    'manage_options',
    SETTINGS_PAGE,
    [ns('Populate'),'settings_page'],
  );
}

function setup_section($section, $label)
{
  log_info("setup_section($page,$section,$label)");
  add_settings_section(
    $section,
    $label,
    [ns('Populate'),$section],
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
    [ns('Populate'),$setting],
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

    case SCHEDULE_UPDATE:
      self::text_input(
        $name,
        "Minimum time between querying YouTube for livestream schedule updates",
        "\s*(\d+d)?\s*(\d+h)?\s*(\d+m)?\s*",
        "[#d][#h][#m][#s]",
      );
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

    # TODO : Remove the following
    $timing = json_encode(timing_settings());
    echo "<div>$timing</div>";

    # @@TODO : Remove the following
    echo "<hr>";
    echo "<div style='margin-left:100px;'>";
    $ajax_runs = get_option('tlc_live_ajax_test',0);
    echo "<b>You have run the AJAX test <span id=ajax_run_count>$ajax_runs</span> times.</b>";
    $action = AJAX_HANDLE;
    $nonce = wp_create_nonce(AJAX_HANDLE);
    $link = admin_url("admin-ajax.php?action=$action&nonce=$nonce");
    echo "<div><a class='ajax_count' data-nonce='$nonce' href='$link'>run AJAX test</a></div>";
    echo "</div>";
  }

  public static function settings_test()
  {
    $link = esc_url(admin_url('admin-post.php'));
    $nonce = wp_create_nonce(CONNECTION_TEST);
    $action = CONNECTION_TEST;

    echo "<form action='$link' method='post'>";
    #echo "<input type='hidden' name='nonce' value='$nonce'>";
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

  # @@TODO delete this
  public static function update_test_result()
  {
    log_info("Update_test_result: ".json_encode($_REQUEST));

    if(!wp_verify_nonce($_REQUEST['nonce'],AJAX_HANDLE)) {
      log_info("Bad nonce");
      exit("Bad nonce");
    }

    $cur_count = get_option('tlc_live_ajax_test',0);
    $new_count = $cur_count + 1;
    if(update_option('tlc_live_ajax_test',$new_count)) {
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

