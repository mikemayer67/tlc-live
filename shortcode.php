<?php
namespace TLC\Live;

/**
 * TLC Livestream plugin shortcode setup
 */

if( ! defined('WPINC') ) { die; }

require_once tlc_plugin_path('include/logger.php');
require_once tlc_plugin_path('settings.php');
require_once tlc_plugin_path('youtube.php');

/**
 * handle the plugin shortcode
 *
 * shortcode format: [tag key=value ... ]content[/tag]
 *
 * @param dict $attr shortcode attributes
 * @param string $content shortcode content
 * @param string $tag shortcode tag
 */

function handle_shortcode($attr,$content=null,$tag=null)
{
  wp_enqueue_style('tlc-livestream-shortcode', tlc_plugin_url('css/tlc-livestream-shortcode.css'));

  $settings = Settings::instance();

  $api_key = $settings->get(API_KEY);
  $channel_id = $settings->get(CHANNEL_ID);
  $playlist_id = $settings->get(PLAYLIST_ID);

  $errstr = "<span class='tlc-warning'>";
  $errstr .= 'Invalid TLC Livestream settings. Contact your site administrator.';
  $errstr .= '</span>';

  if( empty($api_key)    ) { return $errstr; }
  if( empty($channel_id) ) { return $errstr; }

  if( ! (new ValidateAPIKey($api_key))->is_valid() ) { return $errstr; }
  if( ! (new ValidateChannelID($channel_id,$api_key))->is_valid() ) { return $errstr; }

  $q = new UpcomingLivestreams($channel_id,$api_key);
  $next_livestream = $q->next_livestream();

  if( $playlist_id ) {
    if( ! (new ValidatePlaylistID($playlist_id,$api_key))->is_valid() ) {
      $playlist_id = null;
    }
  }
  $prev_recorded = null;
  if( $playlist_id) {
    $q = new RecordedLivestreams($playlist_id,$api_key);
    $prev_recorded = $q->most_recent();
  }

  $now = time();

  $attr = shortcode_atts(
    array(
      AUTOPLAY => $settings->get(AUTOPLAY),
      CONTROLS => $settings->get(CONTROLS),
      ENABLE_KB => $settings->get(ENABLE_KB),
      FULL_SCREEN => $settings->get(FULL_SCREEN),
      MODEST_BRANDING => $settings->get(MODEST_BRANDING),
      TRANSITION => $settings->get(TRANSITION),
    ),
    $attr,
  );

  if( $next_livestream and $prev_recorded )
  {
    $transition = $next_livestream['scheduledStart'] - $attr[TRANSITION];
    return '<div>' . print_r($attr,true) . "</div><div>$content</div>" . 
      "<div>" . print_r($next_livestream,true) . "</div><div>" .
      print_r($prev_recorded,true)."</div><div>Now=$now</div>" .
      "<div>Transition=$transition</div>";
  }
  elseif( $next_livestream )
  {
    return '<div>' . print_r($attr,true) . "</div><div>$content</div>" . 
      "<div>" . print_r($next_livestream,true) . "</div>";
  }
  elseif( $prev_recorded )
  {
    return '<div>' . print_r($attr,true) . "</div><div>$content</div>" . 
      "<div>" . print_r($prev_recorded,true) . "</div>";
  }
  else
  {
    return "<span class='tlc-warning'>No upcoming or recorded livestreams found." .
      "  Contact your site administrator.";
  }
  

}

add_shortcode('tlc-livestream', ns('handle_shortcode'));
