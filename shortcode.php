<?php
namespace TLC\Live;

/**
 * TLC Livestream plugin shortcode setup
 */

if( ! defined('WPINC') ) { die; }

require_once tlc_plugin_path('logger.php');

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
  $attr_json = json_encode($attr);
  log_info("handle_shortcode: in: $attr_json");
  $attr = shortcode_atts(
    [
      "switch" => "1m",
    ],
    $attr,
  );
  $attr_json = json_encode($attr);
  log_info("handle_shortcode: filtered: $attr_json");

  return "<b>YouTube Frame will go here</b><div>Attributes: $attr_json</div><div>Content: $content</div><div>Tag: $tag</div>";
}

add_shortcode('tlc-livestream', ns('handle_shortcode'));
