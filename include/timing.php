<?php
namespace TLC\Live;

require_once TLC_LIVESTREAM_DIR.'/admin/settings.php';

function timing_settings()
{
  fill_default_settings();

  return [
    'switch'=>parse_timing(get_option(TIMING_SWITCH)),
    'poll_start'=>parse_timing(get_option(TIMING_POLL_START)),
    'poll_freq'=>parse_timing(get_option(TIMING_POLL_FREQ)),
  ];
}

function parse_timing($t)
{
  $result = Array();
  preg_match('/(?:(\d+)d)?\s*(?:(\d+)h)?\s*(?:(\d+)m)?\s*(?:(\d+)s)?/',$t,$result);
  $n = count($result);

  $rval = 0;
  if($n > 1) {
    $rval += 86400 * (int)($result[1]);
    if($n > 2) {
      $rval += 3600 * (int)($result[2]);
      if($n > 3) {
        $rval += 60 * (int)($result[3]);
        if($n > 4) {
          $rval += (int)($result[4]);
        }
      }
    }
  }

  return $rval;
}
