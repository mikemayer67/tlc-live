<?php
namespace TLC\Live;

require_once tlc_plugin_path('logger.php');

/**
 * Setup and handling of plugin settings
 */

if( ! defined('WPINC') ) { die; }

class Settings
{
  static function activate()
  {
    log_info("Settings::activate: ".__NAMESPACE__);
  }

  static function uninstall()
  {
  }

};



