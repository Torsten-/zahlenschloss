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

$log_fh = null;

function open_log(){
  global $log_fh;

  $log_fh = fopen("logs/".date("Y-m-d").".log","a");

  if(!$log_fh) die("ERROR opening logfile");
  else return true;
}

function write_log($message, $array = null){
  global $log_fh;

  if(is_array($array)){
    unset($array["code"]);
    $message .= " # ".json_encode($array);
  }

  $return = fwrite($log_fh, date("Y-m-d H:i:s")." - ".$_SERVER["REMOTE_ADDR"]." - ".$message."\n");

  if(!$return) die("ERROR writing logfile");
  else return true;
}

function change_state($state){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "http://".HOMEMATIC_IP."/addons/xmlapi/statechange.cgi?ise_id=".HOMEMATIC_ID."&new_value=".$state);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_exec($ch);
  curl_close($ch);
}


function get_state(){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "http://".HOMEMATIC_IP."/addons/xmlapi/state.cgi?channel_id=".HOMEMATIC_ID);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  curl_close($ch);

  $xp = xml_parser_create("ISO-8859-1");
  xml_parse_into_struct($xp, $response, $response_array);
  xml_parser_free($xp);

  foreach($response_array as $val){
    if($val["tag"] == "DATAPOINT" && $val["attributes"]["TYPE"] == "STATE"){
      return $val["attributes"]["VALUE"];
    }
  }

  return "unknown";
}

/*
if($_GET["pin"] == "123456"){
  $state = get_state();
  $new_state = "";
  if($state == "false") $new_state = "true";
  else $new_state = "false";

  change_state($new_state);
  echo "PIN=OK";
}else echo "PIN=NOK";
*/
?>
