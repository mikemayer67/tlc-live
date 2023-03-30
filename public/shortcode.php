<?php
namespace TLC\Live;

function handle_shortcode($attr,$content=null)
{
  $attr = shortcode_atts(
    [ 
      "switch"=>"1m",
    ],
    $attr,
  );
  $attr_json = json_encode($attr);

  return "<b>YouTube Frame will go here</b><div>For now: $attr_json</div>";

}
