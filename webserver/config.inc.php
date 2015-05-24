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

// Encryption Key um die verschluesselte JSON-Ausgabe mit den Pins zu entschluesseln
define('ENCRYPTION_KEY', '');

// URL von der die Pins abgefragt werden sollen
define('PIN_URL', '');

// Datei in der die Pins zwischengespeichert werden
define('PIN_FILE', 'pins.json');

// Homematic-Server IP-Adresse
define('HOMEMATIC_IP', '192.168.142.150');

// Homematic ID des Tuerschloss-Motors
define('HOMEMATIC_ID', '1309');
?>
