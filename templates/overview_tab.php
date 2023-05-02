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

$api_key_is_good = false;
if( empty($api_key) )
{
  $api_icon = $bad;
  $api_reason = 'required';
} else {
  $api_status = new ValidateAPIKey($api_key);
  $api_key_is_good = $api_status->is_valid();
  if( $api_key_is_good ) {
    $api_icon = $ok;
    $api_reason = '';
  } elseif( $api_status->is_invalid() ) {
    $api_icon = $bad;
    $api_reason = $api_status->reason();
  } else {
    $api_icon = $unknown;
    $api_reason = $api_status->reason();
  }
}

$channel_is_good = false;
if( empty($channel) )
{
  $channel_icon = $bad;
  $channel_reason = 'required';
} else {
  $channel_status = new ValidateChannelID($channel,$api_key);
  $channel_is_good = $channel_status->is_valid();
  if( $channel_is_good ) {
    $channel_is_good = true;
    $channel_icon = $ok;
    $channel_reason = $channel_status->title();
  } elseif( $channel_status->is_invalid() ) {
    $channel_icon = $bad;
    $channel_reason = $channel_status->reason();
  } else {
    $channel_icon = $unknown;
    $channel_reason = $channel_status->reason();
  }
}

$playlist_is_good = false;
if( empty($playlist) )
{
  $playlist_icon = $unknown;
  $playlist_reason = '';
} else {
  $playlist_status = new ValidatePlaylistID($playlist,$api_key);
  $playlist_is_good = $playlist_status->is_valid();
  if( $playlist_is_good ) {
    $playlist_icon = $ok;
    $playlist_reason = $playlist_status->title();
  } elseif( $playlist_status->is_invalid() ) {
    $playlist_icon = $bad;
    $playlist_reason = $playlist_status->reason();
  } else {
    $playlist_icon = $unknown;
    $playlist_reason = $playlist_status->reason();
  }
}

$query_freq = floor($settings->get(QUERY_FREQ)/60);

$autoplay = ( $settings->get(AUTOPLAY)
  ? "The stream will <b>automatcally</b> start when it goes live."
  : "The viewer will need to <b>hit the play button</b> in the player to start the livestream."
);

$controls = ( $settings->get(CONTROLS)
  ? "Playback contols <b>will</b> displayed in the player."
  : "Playback contols will <b>not</b> displayed in the player."
);

$enablekb = ($settings->get(ENABLE_KB)
  ? "The viewer <b>will</b> be able to control player with the keyboard."
  : "The viewer will <b>not</b> be able to control player with the keyboard."
);

$fullscreen = ($settings->get(FULL_SCREEN)
  ? "The player <b>can</b> be made to dispaly full screen."
  : "The player <b>cannot</b> be made to dispaly full screen."
);

$modestbranding = ($settings->get(MODEST_BRANDING)
  ? "The YouTube logo will <b>not</b> be shown unless the livestream is paused."
  : "The YouTube logo <b>may</b> be shown while the livestream is playing."
);

$transition = floor($settings->get(TRANSITION)/60);
$transition_m = $transition % 60;
$transition   = ($transition - $transition_m) / 60;
$transition_h = $transition % 24;
$transition_d = ($transition - $transition_h) / 24;

$transition = "";
switch($transition_d) {
case 0:  $transition = "";                   break;
case 1:  $transition = "1 day";              break;
default: $transition = "$transition_d days"; break;
}
switch($transition_h) {
case 0:                                        break;
case 1:  $transition .= " 1 hour";              break;
default: $transition .= " $transition_h hours"; break;
}
switch($transition_m) {
case 0:                                          break;
case 1:  $transition .= " 1 minute";              break;
default: $transition .= " $transition_m minutes"; break;
}

if( empty($transition) ) {
  $transition = "at";
} else {
  $transition = "$transition before";
}

?>


<h2>Connection Settings</h2>
<table class='tlc-overview'>
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
    <td class=status><img src='<?=$playlist_icon?>'></img></td>
    <td class=status><span><?=$playlist_reason?></span></td>
  </tr>
  <tr>
    <td class=info colspan=4>
      YouTube API queries will occur no more frequently than once every 
      <?=($query_freq>1 ? "$query_freq minutes" : "minute")?>.
    </td>
  </tr>
</table>


<h2>Available Playlists</h2>
<table class='tlc-overview'>
<?php if($api_key_is_good and $channel_is_good) { ?>
<?php log_info("API Key: $api_key_is_good"); ?>
<?php log_info("Channl ID: $channel_is_good"); ?>
  <tr>
    <td class=info>
      Flesh this out.
    </td>
  </tr>
<?php } else { ?>
  <tr>
    <td class=warning>
      Can only be shown if both the current API Key and Channel ID are valid.
    </td>
  </tr>
<?php } ?>
</table>



<h2>Livestream Settings</h2>
<table class='tlc-overview'>
  <tr>
    <td class=label>autoplay</td>
    <td class=value><?=$autoplay?></td>
  </tr>
  <tr>
    <td class=label>controls</td>
    <td class=value><?=$controls?></td>
  </tr>
  <tr>
    <td class=label>keyboard</td>
    <td class=value><?=$enablekb?></td>
  </tr>
  <tr>
    <td class=label>full screen</td>
    <td class=value><?=$fullscreen?></td>
  </tr>
  <tr>
    <td class=label>modest branding</td>
    <td class=value><?=$modestbranding?></td>
  </tr>
  <tr>
    <td class=info colspan=2>
      The transition from recorded to upcoming livestream will occur <?=$transition?> 
      the scheduled start time.
    </td>
  </tr>
<?php
?>

