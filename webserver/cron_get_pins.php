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
require("mc.inc.php");

open_log("cron_get_pins.log");

$response = file_get_contents(PIN_URL.mc_secret());
if(!$response){
  write_log("ERROR: Couldn't get pins from ".PIN_URL);
  exit();
}

$decrypted = mc_decrypt($response);
if(!$decrypted){
  write_log("ERROR: Couldn't decrypt response");
  exit();
}

if(empty($decrypted)){
  write_log("ERROR: Decrypted response was empty");
  exit();
}

$file = fopen(PIN_FILE,"w");
if(!$file){
  write_log("ERROR: Couldn't open ".PIN_FILE." for writing");
  exit();
}

if(!fwrite($file, $decrypted)){
  write_log("ERROR: Couldn't write response to ".PIN_FILE);
  exit();
}

fclose($file);
write_log("INFO: ".PIN_FILE." updated");
?>
