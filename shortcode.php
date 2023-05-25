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
  static $first = true;
  if(!$first) {
    return;
  } else {
    $first = false;
  }

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
      'width' => '100%',
    ),
    $attr,
  );

  foreach( array(AUTOPLAY,CONTROLS,ENABLE_KB,FULL_SCREEN,MODEST_BRANDING) as $key )
  {
    if( !$attr[$key] ) { $attr[$key] = 0; }
  }
  
  $width = $attr['width'];
  $width_components = array();
  if(preg_match('/^([\d.]+)\s*(\S*)$/',$width,$width_components)) {
    $height = strval(0.5625*$width_components[1]) . $width_components[2];
  } else {
    $width = '100%';
    $height = '56.25%';
  }

  $tag = null;
  $is_upcoming = false;
  $is_live = false;
  if( $next_livestream and $prev_recorded )
  {
    $transition = $next_livestream['scheduledStart'] - $attr[TRANSITION];
    $id = ($now >= $transition) ? $next_livestream['id'] : $prev_recorded['id'];
  }
  elseif( $next_livestream )
  {
    $id = $next_livestream['id'];
  }
  elseif( $prev_recorded )
  {
    $id = $prev_recorded['id'];
  }
  else
  {
    return "<span class='tlc-warning'>No upcoming or recorded livestreams found." .
      "  Contact your site administrator.";
  }

  $html = "";
  $html .= "<div class='tlc-yt-container' style='width:$width; padding-top:$height;'>"; 
  $html .= "<iframe class='tlc-yt-embed' type='text/html'";
  $html .= " src='https://www.youtube.com/embed/$id?";
  $html .= "autoplay=".$attr[AUTOPLAY];
  $html .= "&controls=".$attr[CONTROLS];
  $html .= "&disablekb=".strval(1-$attr[CONTROLS]);
  $html .= "&fs=".$attr[FULL_SCREEN];
  $html .= "&modestbranding=".$attr[MODEST_BRANDING];
  $html .= "'>";
  $html .= "</iframe>";
  $html .= "</div>";

  return $html;
}

add_shortcode('tlc-livestream', ns('handle_shortcode'));
