<?php
namespace TLC\Live;

require_once TLC_LIVESTREAM_DIR.'/utils/logger.php';

function handle_activate()
{
  log_info("activate ");
}

function handle_deactivate()
{
  log_info("deactivate ");
}

function handle_uninstall()
{
  delete_option(TLC\Live\Settings\YOUTUBE_API_KEY);
  delete_option(TLC\Live\Settings\YOUTUBE_CLIENT_ID);
  delete_option(TLC\Live\Settings\YOUTUBE_CLIENT_SECRET);
  delete_option(TLC\Live\Settings\TIMING_SWITCH);
}
