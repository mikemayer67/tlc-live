<?php

require_once TLC_LIVESTREAM_DIR.'/utils/logger.php';
use function \TLC\Live\log_info;

function youtube_html()
{
  log_info("youtube_html");
} 

function youtube_connection_error_html()
{
  $image_url = plugins_url('images/no_youtube.png',TLC_LIVESTREAM_DIR.'/images');
  $html = "<img src=$image_url alt='Youtube connection failed'>";
  return $html;
}
