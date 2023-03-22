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
  delete_option('tlc_livestream_activate');
  delete_option('tlc_livestream_deactivate');
}
