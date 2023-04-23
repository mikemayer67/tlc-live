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

const API_KEY = 'api_key';
const CHANNEL_ID = 'channel';
const PLAYLIST_ID = 'playlist';
const AUTOPLAY = 'autoplay';
const CONTROLS = 'controls';
const ENABLE_KB = 'enablekb';
const FULL_SCREEN = 'fullscreen';
const MODEST_BRANDING = 'modestbranding';
const TRANSITION = 'transition';
const QUERY_FREQ = 'query_freq';

const OPTION_DEFAULTS = [
  API_KEY => '',
  CHANNEL_ID => '',
  PLAYLIST_ID => '',
  AUTOPLAY => true,
  CONTROLS => true,
  ENABLE_KB => true,
  FULL_SCREEN => true,
  QUERY_FREQ => 300,
  MODEST_BRANDING => true,
  TRANSITION => 1800,
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

  private $_values = array();

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
      try {
        $this->_values = array_replace($this->_values, $options);
      } catch (TypeError $e) {
      } catch (Exception $e) {
      }
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
  public function get($name) {
    return $this->_values[$name] ?? (OPTION_DEFAULTS[$name] ?? null);
  }

  /**
   * set option value
   *
   * Can only be used as admin
   *
   * @param string $name option name to set
   * @param mixed $value option value to set
   */
  public function set($name,$value) {
    if( ! is_admin() ) { return; }
    $this->_values[$name] = $value;
    update_option(OPTIONS_KEY,$this->_values);
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

    if( empty($name) ) {
      $this->_vaues = array();
    } else {
      unset($this->_values[$name]);
    }

    update_option(OPTIONS_KEY,$this->_values);
  }

  /**
   * removes plugin settings from the WP database
   */
  static function uninstall()
  {
    delete_option(OPTIONS_KEY);
  }

};



