<?php
namespace TLC\Live;

if( ! defined('WPINC') ) { die; }

require_once tlc_plugin_path('include/logger.php');
require_once tlc_plugin_path('include/http.php');

class YouTubeQuery
{
  private $_response_code;
  private $_result;
  private $_errno;
  private $_error;

  public function response_code() { return $this->_response_code; }
  public function result()        { return $this->_result;        }
  public function errno()         { return $this->_errno;         }
  public function error()         { return $_errno ? "$_errno [$_error]" : false; }

  public function __construct($resource, $query_args)
  {
    $youtube_api = "https://youtube.googleapis.com/youtube/v3/$resource";
    $query = http_build_query( $query_args );
    $url = $youtube_api . '?' . $query;

    log_info("YouTubeQuery URL: $url");

    $ch = curl_init($url);
    curl_setopt_array($ch,
      array(
        CURLOPT_TIMEOUT => 5,
        CURLOPT_RETURNTRANSFER => true,
      )
    );

    $this->_result = curl_exec($ch);
    $this->_response_code = curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
    $this->_errno = curl_errno($ch);
    $this->_error = curl_error($ch);
    curl_close($ch);

    log_info("YouTubeQuery result: ".$this->_result);

    if( $this->_errno ) {
      log_error('YouTube Query Failure');
      log_error("    URL=$url");
      log_error('    errno='.$this->_errno.' ['.$this->_error.']');
      log_error('    response_code='.$this->_response_code);
      log_error('    result='.$this->_result);
      log_error('    content_type='.curl_getinfo($ch,CURLINFO_CONTENT_TYPE));
    }
  }
}

class ValidationQuery extends YouTubeQuery
{
  const VALID = 0;
  const INVALID = 1;
  const UNKNOWN = 2;
  const MISSING = 3;

  protected $_state;
  protected $_reason;

  public function is_valid()   { return $this->_state == self::VALID;   }
  public function is_invalid() { return $this->_state == self::INVALID; }
  public function is_unknown() { return $this->_state == self::UNKNOWN; }
  public function is_missing_param() { return $this->_state == self::MISSING; }

  public function reason() { return $this->_reason; }

  public function __construct($resource,$query_args) {
    $this->_state = self::UNKNOWN;
    $this->_reason = "Query not run";

    parent::__construct($resource,$query_args);
  }
}

class ValidateAPIKey extends ValidationQuery
{
  public function __construct($api_key)
  {
    if(empty($api_key)) {
      $this->_state = self::INVALID;
      $this->_reason = "required";
      return;
    }

    parent::__construct(
      "videos",
      array(
        'part' => 'id',
        'chart' => 'mostPopular',
        'maxResults' => 1,
        'regionCode' => 'US',
        'key' => $api_key,
      )
    );

    $rc = $this->response_code();
    if( $this->errno() ) {
      $this->_state = self::UNKNOWN;
      $this->_reason = $this->error();
    } elseif($rc == 200) {
      $this->_state = self::VALID;
    } elseif( $rc == 400 || $rc == 403 ) {
      $this->_state = self::INVALID;
      $this->_reason = "Invalid API Key";
    } else {
      $this->_state = self::UNKNOWN;
      $rc_text = http_response_code_string($rc);
      $this->_reason = "Internal error: $rc [$rc_text]";
    }
  }
}

class ValidateChannelID extends ValidationQuery
{
  private $_title;
  public function title() { return $this->is_valid() ? $this->_title : ''; }

  public function __construct($channel_id, $api_key)
  {
    $this->_title = "";

    if(empty($channel_id)) {
      $this->_state = self::INVALID;
      $this->_reason = "required";
      return;
    }
    if(empty($api_key)) {
      $this->_state = self::MISSING;
      $this->_reason = "API Key needed to validate channel ID";
      return;
    }

    parent::__construct(
      "channels",
      array(
        'part' => 'id,snippet',
        'id' => $channel_id,
        'fields' => 'items(id, snippet(title))',
        'key' => $api_key,
      )
    );

    $rc = $this->response_code();
    if( $this->errno() ) {
      $this->_state = self::UNKNOWN;
      $this->_reason = $this->error();
    } elseif($rc == 200) {
      $result = json_decode($this->result(),true);
      if(empty($result)) {
        $this->_state = self::INVALID;
        $this->_reason = 'Invalid Channel ID';
      } else {
        $this->_state = self::VALID;
        $this->_title = $result['items'][0]['snippet']['title'];
      }
    } elseif( $rc == 400 || $rc == 403 ) {
      $this->_state = self::UNKNOWN;
      $this->_reason = "Valid API Key needed to validate channel ID";
    } else {
      $this->_state = self::UNKNOWN;
      $rc_text = http_response_code_string($rc);
      $this->_reason = "Internal error: $rc [$rc_text]";
    }
  }
}

