<?php
namespace TLC\Live;

require_once tlc_plugin_path('include/logger.php');
require_once tlc_plugin_path('settings.php');
require_once tlc_plugin_path('youtube.php');

$settings = Settings::instance();
$api_key = $settings->get(API_KEY);
$channel = $settings->get(CHANNEL_ID);
$playlist = $settings->get(PLAYLIST_ID);

$ok = tlc_plugin_url('images/icons8-valid.png');
$bad = tlc_plugin_url('images/icons8-invalid.png');
$unknown = tlc_plugin_url('images/icons8-unknown.png');

$api_status = new ValidateAPIKey($api_key);
if( $api_status->is_valid() ) {
  $api_icon = $ok;
  $api_reason = '';
} elseif( $api_status->is_invalid() ) {
  $api_icon = $bad;
  $api_reason = $api_status->reason();
} else {
  $api_icon = $unknown;
  $api_reason = $api_status->reason();
}

$channel_status = new ValidateChannelID($channel,$api_key);
if( $channel_status->is_valid() ) {
  $channel_icon = $ok;
  $channel_reason = $channel_status->title();
} elseif( $channel_status->is_invalid() ) {
  $channel_icon = $bad;
  $channel_reason = $channel_status->reason();
} else {
  $channel_icon = $unknown;
  $channel_reason = $channel_status->reason();
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
