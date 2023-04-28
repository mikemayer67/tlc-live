<?php
namespace TLC\Live;

require_once tlc_plugin_path('logger.php');
require_once tlc_plugin_path('settings.php');
require_once tlc_plugin_path('youtube.php');

$settings = Settings::instance();
$api_key = $settings->get(API_KEY);
$channel = $settings->get(CHANNEL_ID);
$playlist = $settings->get(PLAYLIST_ID);

$ok = tlc_plugin_url('images/icons8-ok.png');
$bad = tlc_plugin_url('images/icons8-cancel.png');

$api_status = validate_api_key($api_key);
if( $api_status['valid'] ){
  $api_icon = $ok;
  $api_reason = '';
} else {
  $api_icon = $bad;
  $api_reason = $api_status['reason'];
}

?>

<h2>Connection Parameters</h2>
<table class='tlc-test'>
  <tr>
    <td class=label>YouTube API Key</td>
    <td class=value><?=$api_key?></td>
    <td class=status><img src='<?=$api_icon?>'></img></td>
    <td class=status><span><?=$api_reason?></span></td>
  </tr>
  <tr>
    <td class=label>Channel ID</td>
    <td class=value><?=$channel?></td>
    <td class=status><img src='<?=$bad?>'></img></td>
  </tr>
  <tr>
    <td class=label>Playlist ID</td>
    <td class=value><?=$playlist?></td>
  </tr>
</table>
