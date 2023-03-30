<?php
namespace TLC\Live;

require_once TLC_LIVESTREAM_DIR.'/include/logger.php';
require_once TLC_LIVESTREAM_DIR.'/admin/settings.php';

function handle_activate()
{
  log_info("activate");
  fill_default_settings();
}

function handle_deactivate()
{
  log_info("deactivate ");
}

function handle_uninstall()
{
  clear_all_settings();
}
