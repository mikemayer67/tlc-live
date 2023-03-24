<?php
namespace TLC\Live\Public;

require_once('youtube.php');

function handle_shortcode($attr,$content=null)
{
  $attr = shortcode_atts(
    [ 
      "switch"=>"1m",
    ],
    $attr,
  );
  $attr_json = json_encode($attr);

  $content = youtube_html();

  if($content == null) {
    return youtube_connection_error_html();
  }

  return $content;
}
