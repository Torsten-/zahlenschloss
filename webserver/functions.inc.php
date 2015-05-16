<?php
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
  global $homeatic_ip, $homeatic_id;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "http://".$homeatic_ip."/addons/xmlapi/statechange.cgi?ise_id=".$homeatic_id."&new_value=".$state);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_exec($ch);
  curl_close($ch);
}


function get_state(){
  global $homeatic_ip, $homeatic_id;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "http://".$homeatic_ip."/addons/xmlapi/state.cgi?channel_id=".$homeatic_id);
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
