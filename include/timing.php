<?php
namespace TLC\Live;

require_once TLC_LIVESTREAM_DIR.'/include/logger.php';

const SCHEDULE_UPDATE = 'tlc_live_schedule_update';
const TIMING_SWITCH = 'tlc_live_swith_to_upcoming';
const TIMING_POLL_START = 'tlc_live_poll_start';
const TIMING_POLL_FREQ = 'tlc_live_poll_freq';

const DEFAULT_SCHEDULE_UPDATE = '5m';
const DEFAULT_TIMING_SWITCH = '30m';
const DEFAULT_TIMING_POLL_START = '5m';
const DEFAULT_TIMING_POLL_FREQ = '10s';

function fill_timing_defaults()
{
  update_option(SCHEDULE_UPDATE,  get_option(SCHEDULE_UPDATE,  DEFAULT_SCHEDULE_UPDATE));
  update_option(TIMING_SWITCH,    get_option(TIMING_SWITCH,    DEFAULT_TIMING_SWITCH));
  update_option(TIMING_POLL_START,get_option(TIMING_POLL_START,DEFAULT_TIMING_POLL_START));
  update_option(TIMING_POLL_FREQ, get_option(TIMING_POLL_FREQ, DEFAULT_TIMING_POLL_FREQ));
}

function clear_timing_settings()
{
  delete_option(TIMING_SWITCH);
  delete_option(TIMING_POLL_START);
  delete_option(TIMING_POLL_FREQ);
}

function timing_settings()
{
  fill_default_settings();

  return [
    'update_freq' => parse_timing(get_option(SCHEDULE_UPDATE)),
    'switch'      => parse_timing(get_option(TIMING_SWITCH)),
    'poll_start'  => parse_timing(get_option(TIMING_POLL_START)),
    'poll_freq'   => parse_timing(get_option(TIMING_POLL_FREQ)),
  ];
}

function parse_timing($t)
{
  $match = Array();
  preg_match('/(?:(\d+)d)?\s*(?:(\d+)h)?\s*(?:(\d+)m)?\s*(?:(\d+)s)?/',$t,$match);

  $t = array_pad($match,5,0);
  $day = (int)($t[1]);
  $hour = (int)($t[2]);
  $min = (int)($t[3]);
  $sec = (int)($t[4]);

  return $sec + 60*($min + 60*($hour + 24*$day));
}
