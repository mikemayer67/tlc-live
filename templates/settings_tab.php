<?php
namespace TLC\Live;

require_once tlc_plugin_path('logger.php');
require_once tlc_plugin_path('settings.php');

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

?>

<form id='tlc-livestream-settings' class='tlc' method="POST">
  <input type="hidden" name="action" value="update">
  <?=$nonce?>
  <div class=tlc>
    <div class=label>YouTube API Key</div>
    <div class='info'>
      The YouTube API key is used to query your YouTube channel to examine your playlist
      to determine the most recently recorded livestream.  You must create this key
      via the <a href='https://console.cloud.google.com' target='_blank' rel='noopener noreferrer'>Google Cloud console</a>.
    </div>
    <div class=input>
      <input type="text" class=tlc name=<?=API_KEY?> value="<?=$api_key?>" pattern='^[a-zA-Z0-9_]*$'>
    </div>
    <div class=label>Channel ID</div>
    <div class=info>
      The YouTube channel ID is used to look for your active and upcoming livestreams.  If the channel ID is not set or
      contains an invalid value, the shortcode will not render any output.
    </div>
    <div class=info>
      You can find your channel ID from your 
      <a href='https://youtube.com/account_advanced' target='_blank' rel='noopener noreferrer'>advanced YouTube account settings</a>.
    </div>
    <div class=input>
      <input type="text" name=<?=CHANNEL_ID?> value="<?=$channel?>" pattern='^[a-zA-Z0-9_]*$'>
    </div>
    <div class=label>Playlist ID</div>
    <div  class=info>
      The Playlist ID is used to determine what to show when there is not an active livestream and it is not yet
      time to start displaying the next upcoming livestream.  If the Playlist ID is not set or contains
      an invalid value, only the active or next upcoming livestream will be displayed.
    </div>
    <div id='tlc-playlist-info' class=info>
      You can find your playlist from your YouTube channel:
      <ul>
        <li>Go to <a href='https://youtube.com' target='_blank' rel='noopener noreferrer'>Youtube</a></li>
        <li>Sign in (<i>upper right corner</i>) if not already logged in</li>
        <li>Select "Your Channel" under the user icon in the upper right corner.</li>
        <li>Click on "Playlists"</li>
        <li>Click on the "View full playlist" under the playlist you want to use.</li>
        <li>Look at the url. You will see the playlist ID following "list="</li>
      </ul>
    </div>
    <div class=input>
      <td><input type="text" name=<?=PLAYLIST_ID?> value="<?=$playlist?>" pattern='^[a-zA-Z0-9_]*$'></td>
    </div>
    <div class=label>Transition To Upcoming Livestream</div>
    <div class=info>
      The transition setting determines how far in advance of an upcoming livestream
      your website will switch from embedding the most recently recorded livestream
      to embedding the upcoming livestream.  If left blank, the transition will occur
      at the scheduled start time.
    </div>
    <div class=input>
      <input type='text' class='tls-time' name='transition_d' value="<?=$transition_d?>" pattern='^\s*\d*\s*$'>
      days
      <input type='text' class='tls-time' name='transition_h' value="<?=$transition_h?>" pattern='^\s*\d*\s*$'>
      hours
      <input type='text' class='tls-time' name='transition_m' value="<?=$transition_m?>" pattern='^\s*\d*\s*$'>
      minutes
    </div>
    <div class=label>Embedded Livestream Options</div>
    <div class=info>
      There are a number of options that can be configured when embedding a YouTube video within
      a webpage.  For more information on each of these, see
      <a href='https://developers.google.com/youtube/player_parameters#Parameters' target='_blank' rel='noopener noreferrer'>Player Parameters</a> in the 
      <a href='https://developers.google.com/youtube' target='_blank' rel='noopener noreferrer'>Youtube developer documentaiton</a>
    </div>
    <div class=input>
      <ul>
        <li>
          <input type=checkbox name=<?=AUTOPLAY?> value=1 <?= $autoplay?'checked':''?>>
          <span class=input-label>autoplay</span>
          <span class=input-info>
            playback will occur without any user interaction with the player
          </span>
          </input>
        </li>
        <li>
          <input type=checkbox name=<?=CONTROLS?> value=1 <?= $controls?'checked':''?>>
          <span class=input-label>controls</span>
          <span class=input-info>
            playback controls are displayed in the player
          </input>
        </li>
        <li>
          <input type=checkbox name=<?=ENABLE_KB?> value=1 <?= $enablekb?'checked':''?>>
          <span class=input-label>enable keyboard</span>
          <span class=input-info>
            keyboard can be used to control playback
          </span>
          </input>
        </li>
        <li>
          <input type=checkbox name=<?=FULL_SCREEN?> value=1 <?= $fullscreen?'checked':''?>>
          <span class=input-label>full screen</span>
          <span class=input-info>
            allow the user to display the livestream full screen
          </span>
          </input>
        </li>
        <li>
          <input type=checkbox  name=<?=MODEST_BRANDING?> value=1 <?=$modestbranding?'checked':''?>>
          <span class=input-label>modest branding</span>
          <span class=input-info>
            suppress the YouTube logo from the livestream unless paused
          </span>
          </input>
        </li>
      </ul>
    </div>
    <div class=label>YouTube Query Frequency</div>
    <div class=info>
      YouTube API queries are subject to a quota system, allowing only so many queries per day.
      If you are using the API key specified above for more than just this plugin, you may wish
      to reduce the query frequency to avoid hitting quota limitations.  Otherwise, the default
      limit of no more than one query every 5 minutes should be sufficient to avoid quota issues.
    </div>
    <div class=input>
      <input type='text' class='tls-time' name=<?=QUERY_FREQ?> value="<?=$query_freq?>" pattern='^\s*\d*\s*$'>
      minutes
    </div>
  </div>
  <input type="submit" value="Save" class="submit button button-primary button-large">
</form>