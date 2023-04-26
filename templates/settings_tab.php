<?php
namespace TLC\Live;
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
