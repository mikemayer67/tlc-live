<?php
namespace TLC\Live;

require_once TLC_LIVESTREAM_DIR.'/include/logger.php';

function add_css($handle)
{
  log_info("add_css($handle)");

  wp_enqueue_style(
    $handle,
    TLC_LIVESTREAM_URL."/css/$handle.css",
  );
}
