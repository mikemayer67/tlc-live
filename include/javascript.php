<?php
namespace TLC\Live;

require_once TLC_LIVESTREAM_DIR.'/include/logger.php';

function add_javascript($handle)
{
  log_info("add_javascript($handle)");

  wp_register_script(
    $handle,
    TLC_LIVESTREAM_URL."/js/$handle.js",
    ['jquery'],
  );

  wp_localize_script(
    $handle,
    'tlcAjax',
    [ 'ajaxurl' => admin_url('admin-ajax.php') ],
  );

  wp_enqueue_script('jquery');
  wp_enqueue_script($handle);
}
