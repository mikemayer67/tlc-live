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

function handle_admin_init()
{
  log_info("Settings::init ");
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
}

function handle_admin_menu()
{
  log_info("Settings::menu ");
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
?>
<div style="font-weight:bold;">
Enter the time to switch to upcoming livestream using the format '#d #h #m #s'.
</div><div styel="padding-left:1em;">
- You do not need to specify all elements, but they must be specified in the
order shown.  
</div><div styel="padding-left:1em;">
- If left blank, the switch will occur at the scheduled start time.
</div>
<?php
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
  }
}

