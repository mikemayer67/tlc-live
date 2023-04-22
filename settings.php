<?php
namespace TLC\Live;

require_once tlc_plugin_path('logger.php');

/**
 * Setup and handling of plugin settings
 *
 * The plugin settings are stored as a single json encoded dictionary in the WP options database.
 * It contains the following fields (which may or may not be present):
 *
 * Livestreaming timing:
 *
 *   - MIN_UPDATE_FREQ (seconds): minimum wait time between YouTube API schedule queries
 *   - SWITCH_TO_UPCOMING (seconds): when to switch from recorded to upcoming livestream
 *   - START_POLLING (seconds): when to start polling for going live
 *   - POLL_FREQ (seconds): how often to check if upcoming stream is live
 */

if( ! defined('WPINC') ) { die; }

const OPTIONS_KEY = 'tlc_livestream_options';

const MIN_UPDATE_FREQ = 'min_update_freq';
const SWITCH_TO_UPCOMING = 'switch_to_upcoming';
const START_POLLING = 'start_polling';
const POLL_FREQ = 'poll_freq';

const OPTION_DEFAULTS = [
  MIN_UPDATE_FREQ => 300,
  SWITCH_TO_UPCOMING => 1800,
  START_POLLING => 300,
  POLL_FREQ => 10,
];

class Settings
{
  /**
   * singleton instance
   */
  private static $_instance = null;

  /**
   * values dictionary
   */

  private $_values = OPTION_DEFAULTS;

  /**
   * return singleton instanceo
   */
  static function instance() {
    if( self::$_instance == null ) {
      self::$_instance = new self;
    }
    return self::$_instance;
  }

  /**
   * (private) constructor
   *
   * Instantiates values from the WP database
   */
  private function __construct() {
    $options = get_option(OPTIONS_KEY,null);
    if( isset($options) ) {
      $options = json_decode($options);
      $this->_values = array_replace($this->_values, $options);
    }
    log_info("Settings:: $this");
  }

  /**
   * get option value
   *
   * Returns null if the option isn't currently set
   *
   * @param string $name option name to retrieve
   * @return string or null
   */
  public function __get($name) {
    log_info("Settings::get($name)");
    return $this->_values[$name] ?? null;
  }

  /**
   * set option value
   *
   * Can only be used as admin
   *
   * @param string $name option name to set
   * @param mixed $value option value to set
   */
  public function __set($name,$value) {
    if( ! is_admin() ) { return; }
    $this->_values[$name] = $value;
    update_option(OPTIONS_KEY,json_encode($this->_values));
  }

  /**
   * convert values to json string
   *
   * @return string 
   */
  public function __toString() {
    return json_encode($this->_values);
  }


  /**
   * reset option value
   *
   * Can only be used as admin
   *
   * Returns option to default value if one exists
   * Clears option if no default value exists
   *
   * Resets all options to default values if no name is specified
   *
   * @param optional string $name option name to reset
   */
  function reset($name=null) {
    if( ! is_admin() ) { return; }

    log_info("Settings::reset($name)");

    if( empty($name) ) {
      $this->_vaues = OPTION_DEFAULTS;
    } else {
      $default = OPTION_DEFAULTS[$name] ?? null;
      if( isset($default) ) 
      {
        $this->_values[$name] = $default;
      } 
      elseif( isset($this->_values[$name]) ) 
      {
        unset($this->_values[$name]);
      }
    }

    update_option(OPTIONS_KEY,json_encode($this->_values));
  }

  /**
   * removes plugin settings from the WP database
   */
  static function uninstall()
  {
    delete_option(OPTIONS_KEY);
  }

};



