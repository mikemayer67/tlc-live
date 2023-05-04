<?php
namespace TLC\Live;

$title = esc_html(get_admin_page_title());
$status = "";

if(($_POST['action'] ?? null) == "update") {
  require_once tlc_plugin_path('include/logger.php');
  require_once tlc_plugin_path('settings.php');

  $status = "<span class='tlc-status'>udpated</span>";

  if( !wp_verify_nonce($_POST['_wpnonce'],SETTINGS_NONCE) ) { 
    log_error("failed to validate nonce");
    wp_die("Bad nonce");
  }

  $settings = Settings::instance();

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
}

?>

<h1>
<?=$title?><?=$status?>
</h1>

<div class='nav-tab-wrapper'>

<?php
$tabs = [
  ['overview','Overview'],
  ['settings','Settings'],
  ['shortcode','Shortcode'],
];

foreach($tabs as $tab) {
  $class = 'nav-tab';
  $tab_key = $tab[0];
  $tab_label = $tab[1];
  if($cur_tab == $tab_key) {
    $class .= ' nav-tab-active';
  }
  $uri = "{$_SERVER['REQUEST_URI']}&tab={$tab_key}";
  echo "<a class='$class' href='$uri'>$tab_label</a>";
}
?>

</div>
