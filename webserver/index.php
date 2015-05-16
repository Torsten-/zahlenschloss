<?php
require("config.inc.php");
require("functions.inc.php");

open_log();

if(empty($_GET["pin"]) || !is_numeric($_GET["pin"])){
  write_log("ERROR: Pin not set or not numeric: ".$_GET["pin"]);
  die("PIN=NOK");
}
$given_pin = $_GET["pin"];

$file = file_get_contents($pin_file);
if(!$file){
  write_log("ERROR: Couldn't open ".$pin_file);
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
