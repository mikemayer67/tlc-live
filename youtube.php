<?php
namespace TLC\Live;

if( ! defined('WPINC') ) { die; }

require_once tlc_plugin_path('include/logger.php');
require_once tlc_plugin_path('include/http.php');

class Query
{
  private $_result;
  private $_response_code;
  private $_errno;

  public function ok()       { return $this->_response_code == 200;          }
  public function bad_id()   { return floor($this->_response_code/100) == 4; }
  public function result()   { return $this->_result; }

  public function error() {
    if( $this->ok() ) { 
      return null; 
    } elseif( $this->_response_code ) {
      $rc = $this->_response_code; 
      $codestring = http_response_code_string($rc);
      return "query error - $rc [$codestring]";
    } elseif( $this->_errno ) { 
      $errno = $this->_errno;
      $error = curl_strerror($errno);
      return "Internal error - $errno [$error]";
    } else {
      return 'internal error - unknown';
    }
  }

  public function __construct($url)
  {
    log_info("Query URL: $url");

    $ch = curl_init($url);
    curl_setopt_array($ch,
      array(
        CURLOPT_TIMEOUT => 5,
        CURLOPT_RETURNTRANSFER => true,
      )
    );

    $result = curl_exec($ch);

    $this->_errno = curl_errno($ch);
    $this->_response_code = curl_getinfo($ch,CURLINFO_RESPONSE_CODE);

    if( $this->_response_code == 200 )
    {
      $this->_result = json_decode($result,true);
      if(is_null($this->_result)) {
        $this->_result = array("content"=>$result);
      }
    } else {
      $this->_result = array();
    }

    curl_close($ch);

    log_info("Query result: ".json_encode($this->_result));
  }
}

class YouTubeQuery extends Query
{
  public function __construct($resource, $query_args)
  {
    $youtube_api = "https://youtube.googleapis.com/youtube/v3/$resource";
    $query = http_build_query( $query_args );
    $url = $youtube_api . '?' . $query;

    parent::__construct($url);
  }
}

class ValidationQuery extends YouTubeQuery
{
  const VALID = 0;
  const INVALID = 1;
  const UNKNOWN = 2;

  protected $_state;
  protected $_reason;

  public function is_valid()   { return $this->_state == self::VALID;   }
  public function is_invalid() { return $this->_state == self::INVALID; }
  public function is_unknown() { return $this->_state == self::UNKNOWN; }

  public function reason() { return $this->_reason; }

  public function __construct($resource,$query_args) {
    $this->_state = self::UNKNOWN;
    $this->_reason = "Query not run";

    parent::__construct($resource,$query_args);

    if( $this->ok() ) {
      $this->_state = self::VALID;
    } elseif( $this->bad_id() ) {
      $this->_state = self::INVALID;
      $this->_reason = "Invalid API Key";
    } else {
      $this->_state = self::UNKNOWN;
      $this->_reason = $this->error();
    }
  }
}

class ValidateAPIKey extends ValidationQuery
{
  public function __construct($api_key)
  {
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

  }
}

class ValidateChannelID extends ValidationQuery
{
  private $_title;
  public function title() { return $this->is_valid() ? $this->_title : ''; }

  public function __construct($channel_id, $api_key)
  {
    $this->_title = "";

    if(empty($api_key)) {
      $this->_state = self::UNKNOWN;
      $this->_reason = "API Key needed to validate channel ID";
      return;
    }

    parent::__construct(
      "channels",
      array(
        'part' => 'id,snippet',
        'id' => $channel_id,
        'fields' => 'pageInfo,items(id, snippet(title))',
        'key' => $api_key,
      )
    );

    if( $this->ok() ) {
      $result = $this->result();
      if( $result['pageInfo']['totalResults'] < 1 ) {
        $this->_state = self::INVALID;
        $this->_reason = 'Unrecognized Channel ID';
      } else {
        $this->_state = self::VALID;
        $this->_title = $result['items'][0]['snippet']['title'];
      }
    } elseif( $this->bad_id() ) {
      $this->_state = self::UNKNOWN;
      $this->_reason = "Valid API Key needed to validate channel ID";
    }
  }
}

class ValidatePlaylistID extends ValidationQuery
{
  private $_title;
  public function title() { return $this->is_valid() ? $this->_title : ''; }

  public function __construct($playlist_id, $api_key)
  {
    $this->_title = "";

    if(empty($api_key)) {
      $this->_state = self::UNKNOWN;
      $this->_reason = "API Key needed to validate playlist ID";
      return;
    }

    parent::__construct(
      "playlists",
      array(
        'part' => 'snippet',
        'id' => $playlist_id,
        'fields' => 'pageInfo,items(snippet(title))',
        'key' => $api_key,
      )
    );

    if( $this->ok() ) {
      $result = $this->result();
      if( $result['pageInfo']['totalResults'] < 1 ) {
        $this->_state = self::INVALID;
        $this->_reason = 'Unrecognized Playlist ID';
      } else {
        $this->_state = self::VALID;
        $this->_title = $result['items'][0]['snippet']['title'];
      }
    } elseif( $this->bad_id() ) {
      $this->_state = self::UNKNOWN;
      $this->_reason = "Valid API Key needed to validate channel ID";
    }
  }
}

class YouTubeListQuery extends YouTubeQuery
{
  private $_items;

  public function items() { return $this->_items; }

  public function __construct($resource, $query_args)
  {
    $query_args['maxResults'] = 2;
    $fields = $query_args['fields'] ?? "";
    $query_args['fields'] = "nextPageToken,$fields";

    parent::__construct($resource,$query_args);

    if( ! $this->ok() ) { return; }

    $result = $this->result();
    $this->_items = $result['items'];

    while(array_key_exists('nextPageToken',$result)) 
    {
      $query_args['pageToken'] = $result['nextPageToken'];
      $next_query = new YouTubeQuery($resource,$query_args);

      if( ! $next_query->ok() ) { return; }

      $result = $next_query->result();
      $n = count($this->_items);
      array_splice($this->_items,$n,0,$result['items']);
    }
  }
}

class PlaylistIDs extends YouTubeListQuery
{
  private $_playlists;

  public function playlists() { return $this->_playlists; }

  public function __construct($channel_id, $api_key)
  {
    parent::__construct(
      "playlists",
      array(
        'part' => 'snippet',
        'channelId' => $channel_id,
        'fields' => 'items(id,snippet(title))',
        'key' => $api_key,
      )
    );

    if( ! $this->ok() ) { return; }

    $this->_playlists = array();

    foreach( $this->items() as &$item ) {
      $id = $item['id'];
      $title = $item['snippet']['title'];
      $this->_playlists[$id] = $title;
    }
  }
  
}


