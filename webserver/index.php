<?php
/*
  https://github.com/Torsten-/zahlenschloss

  Copyright (C) 2015 Zahlenschloss
  Torsten Amshove <torsten@amshove.net>

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along
  with this program; if not, write to the Free Software Foundation, Inc.,
  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

require("config.inc.php");
require("functions.inc.php");

open_log();

if(empty($_GET["pin"]) || !is_numeric($_GET["pin"])){
  write_log("ERROR: Pin not set or not numeric: ".$_GET["pin"]);
  die("PIN=NOK");
}
$given_pin = $_GET["pin"];

$file = file_get_contents(PIN_FILE);
if(!$file){
  write_log("ERROR: Couldn't open ".PIN_FILE);
  die("PIN=NOK");
}

$pins = json_decode($file,true);
if(!$pins){
  write_log("ERROR: Couldn't decode JSON");
  die("PIN=NOK");
}

foreach($pins as $pin){
  $startdate = strtotime($pin["startdate"]);
  $enddate = strtotime($pin["enddate"]);

  if(!$startdate || !$enddate || $startdate < 1 || $enddate < 1){
    write_log("WARNING: Pin-Entry has a wrong start- or enddate - startdate: ".$pin["startdate"]." - enddate: ".$pin["enddate"]);
    continue;
  }

  if($given_pin == $pin["code"]){
    $now = time();
    if($now > $startdate && $now < $enddate){
      write_log("INFO: Pin accepted", $pin);
      exit("PIN=OK");
    }else{
      write_log("WARNING: Pin was correct but out of timerange", $pin);
      exit("PIN=NOK");
    }
  }
}

write_log("WARNING: Pin not found: ".$given_pin);
exit("PIN=NOK");
?>
