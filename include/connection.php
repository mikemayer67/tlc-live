<?php
namespace TLC\Live;

require_once TLC_LIVESTREAM_DIR.'/include/logger.php';

const YOUTUBE_API_KEY = 'tlc_live_youtube_api_key';
const YOUTUBE_CLIENT_ID = 'tlc_live_youtube_client_id';
const YOUTUBE_CLIENT_SECRET = 'tlc_live_youtube_client_secret';

const CONNECTION_TEST = 'tlc-live-test';


function fill_connection_defaults()
{
}


function clear_connection_settings()
{
  delete_option(YOUTUBE_API_KEY);
  delete_option(YOUTUBE_CLIENT_ID);
  delete_option(YOUTUBE_CLIENT_SECRET);
}

function handle_connection_test()
{
  log_info("handle_connection_test: ".json_encode($_REQUEST));
#   if( !wp_verify_nonce($_REQUEST['nonce'],CONNECTION_TEST) ) {
#     log_warning("Bad nonce");
#     exit("Shame on you...");
#   }

  $api_key = get_option(YOUTUBE_API_KEY,"");
  $client_id = get_option(YOUTUBE_CLIENT_ID,"");
  $client_secret = get_option(YOUTUBE_CLIENT_SECRET,"");
  $referer_url = $_SERVER['HTTP_REFERER'];

  $result = update_livestream_schedule(True);
  log_info("Result: $result");

  echo "<div style='font-family:sans-serif;'>";
  log_info("check");
  echo "<h1>TLC Livestream Plugin</h1>";
  log_info("check");
  echo "<h2>Connection Test</h2>";
  log_info("check");
  echo "<table>";
  log_info("check");
  echo "<tr><th>API Key</th><td>$api_key</td></tr>";
  log_info("check");
  echo "<tr><th>Client ID</th><td>$client_id</td></tr>";
  log_info("check");
  echo "<tr><th>Client Secret</th><td>$client_secret</td></tr>";
  log_info("check");
  echo "<tr><th></th><td>";
  log_info("check");
  echo "<div style='font-size:large;font-weight:bold;padding-top:30px'>";
  log_info("check");
  echo "<a href='$referer_url'>Return to settings page</a></h2>";
  log_info("check");
  echo "</div></td>";
  log_info("check");
  echo "</tr></table>";
  log_info("check");
  echo "</div>";

  log_info("echo done");

  echo $result;
}

function update_livestream_schedule($force=False)
{
  log_info("update_livestream_schedule(force=$force)");
  session_start();
  log_info("Session started: ".json_encode($_SESSION));

  $api_key = get_option(YOUTUBE_API_KEY,"");
  $client_id = get_option(YOUTUBE_CLIENT_ID,"");
  $client_secret = get_option(YOUTUBE_CLIENT_SECRET,"");

  # need to run this from TLC_LIVESTREAM_DIR: composer require google/apiclient:~2.0
  require_once TLC_LIVESTREAM_DIR.'/vendor/autoload.php';

  $client = new \Google_Client();
  log_info("google client created");
  $client->setClientId($client_id);
  log_info("client id set: $client_id");
  $client->setClientSecret($client_secret);
  log_info("client secret set: $client_secret");
  $client->setScopes('https://www.googleapis.com/auth/youtube.readonly');
  log_info("scope set");
  $action = $_REQUEST['action'];
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?action=$action";
  $redirect = filter_var($redirect, FILTER_SANITIZE_URL);
  log_info("redirect assembled: $redirect");
  $client->setRedirectUri($redirect);

  $youtube = new \Google_Service_YouTube($client);
  log_info("youtube client created");

  // Check if an auth token exists for the required scopes
  $tokenSessionKey = 'token-' . $client->prepareScopes();
  log_info("tokey session key: $tokenSessionKey");
  log_info("GET: ".json_encode($_GET));
  if (isset($_GET['code'])) {
    log_info("GET has code");
    log_info("GET state: ".strval($_GET['state']));
    log_info("SESSION: ".json_encode($_SESSION));
    log_info("SESSION state: ".strval($_SESSION['state']));
    if (strval($_SESSION['state']) !== strval($_GET['state'])) {
      die('The session state did not match.');
    }

    $client->authenticate($_GET['code']);
    log_info("client authenticated");
    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
    log_info("access token for $tokenSessionKey obtained: ".$_SESSION[$tokenSessionKey]);

    header('Location: ' . $redirect);
  }

  log_info("Session: ".json_encode($_SESSION));

  if (isset($_SESSION[$tokenSessionKey])) {
    log_info("Session[$tokenSessioney]: ".$_SESSION[$tokenSessionKey]);
    $client->setAccessToken($_SESSION[$tokenSessionKey]);
    log_info("Access Token set");
  }

  // Check to ensure that the access token was successfully acquired.
  if ($client->getAccessToken()) {
    log_info("access token received");
    try {
      // Execute an API request that lists broadcasts owned by the user who
      // authorized the request.
      $broadcastsResponse = $youtube->liveBroadcasts->listLiveBroadcasts(
        'id,snippet',
        ['mine' => 'true'],
      );
      log_info("broadcast response: ".json_encode($broadcastResponse));

      $htmlBody .= "<h3>Live Broadcasts</h3><ul>";
      foreach ($broadcastsResponse['items'] as $broadcastItem) {
        $htmlBody .= sprintf('<li>%s (%s)</li>', $broadcastItem['snippet']['title'],
            $broadcastItem['id']);
      }
      $htmlBody .= '</ul>';

    } catch (Google_Service_Exception $e) {
      $htmlBody = sprintf('<p>A service error occurred: <code>%s</code></p>',
          htmlspecialchars($e->getMessage()));
    } catch (Google_Exception $e) {
      $htmlBody = sprintf('<p>An client error occurred: <code>%s</code></p>',
          htmlspecialchars($e->getMessage()));
    }

    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
    log_info("Access Token: ".$_SESSION[$tokenSessionKey]);
  } else {
      // If the user hasn't authorized the app, initiate the OAuth flow
    $state = mt_rand();
    $client->setState($state);
    $_SESSION['state'] = $state;
    
    log_info("user authorization required: state=$state");

    $authUrl = $client->createAuthUrl();
    log_info("authorization url: $authUrl");
    $htmlBody = <<<END
    <h3>Authorization Required</h3>
    <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
  }

  log_info("Final HTML: $htmlBody");

  return $htmlBody;
}

