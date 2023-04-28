<?php
namespace TLC\Live;

require_once tlc_plugin_path('logger.php');
require_once tlc_plugin_path('settings.php');
require_once tlc_plugin_path('youtube.php');

$settings = Settings::instance();
$api_key = $settings->get(API_KEY);
$channel = $settings->get(CHANNEL_ID);
$playlist = $settings->get(PLAYLIST_ID);

$ok = tlc_plugin_url('images/icons8-valid.png');
$bad = tlc_plugin_url('images/icons8-invalid.png');
$unknown = tlc_plugin_url('images/icons8-unknown.png');

$api_status = validate_api_key($api_key);
$api_good = $api_status['valid'];
if( $api_good ) {
  $api_icon = $ok;
  $api_reason = '';
} else {
  $api_icon = $bad;
  $api_reason = $api_status['reason'];
}

if($api_good) {
  $channel_status = validate_channel_id($channel,$api_key);
  if( $channel_status['valid'] )
  {
    $channel_icon = $ok;
    $channel_reason = $channel_status['title'];
  } else {
    $channel_icon = $bad;
    $channel_reason = $channel_status['reason'];
  }
}
else
{
  $channel_icon = $unknown;
  $channel_reason = "requires valid API Key";
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
    <td class=status><img src='<?=$channel_icon?>'></img></td>
    <td class=status><span><?=$channel_reason?></span></td>
  </tr>
  <tr>
    <td class=label>Playlist ID</td>
    <td class=value><?=$playlist?></td>
  </tr>
</table>
