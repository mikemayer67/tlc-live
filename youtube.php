<?php
namespace TLC\Live;

if( ! defined('WPINC') ) { die; }

require_once tlc_plugin_path('logger.php');

function validate_api_key($key)
{
  if( empty($key) ) {
    return array("valid"=>false, "reason"=>"required");
  }
  $youtube_api = "https://youtube.googleapis.com/youtube/v3/videos";
  $query = http_build_query( array(
    'part' => 'id',
    'chart' => 'mostPopular',
    'maxResults' => 1,
    'regionCode' => 'US',
    'key' => $key,
  ));
  $url = $youtube_api . '?' . $query;
  $ch = curl_init($url);
  curl_setopt_array($ch,
    array(
      CURLOPT_TIMEOUT => 5,
      CURLOPT_RETURNTRANSFER => true,
    )
  );
  $result = curl_exec($ch);
  $response_code = curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
  $errno = curl_errno($ch);
  $err = curl_error($ch);
  curl_close($ch);

  switch($response_code) {
  case 200:
    return array('valid'=>true);
  case 400:
    return array('valid'=>false, 'reason'=>"Invalid API Key");
  case 403:
    return array('valid'=>false, 'reason'=>"Missing API Key");
  }
  if( $errno != 0 ) {
    log_error("Valid API Key:: $errno [$err]");
    return array('valid'=>false, 'reason'=>"$errno [$err]");
  }

  log_error("Validate API Key:: Unknown Failure");
  log_error("    URL=$url");
  log_error("    result=$result");
  log_error("    errno=$errno [$err]");
  log_error("    response_code=".curl_getinfo($ch,CURLINFO_RESPONSE_CODE));
  log_error("    content_type=".curl_getinfo($ch,CURLINFO_CONTENT_TYPE));

  return array('valid'=>false, 'reason'=>'Internal Error');
}


