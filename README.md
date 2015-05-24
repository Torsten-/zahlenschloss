# zahlenschloss
Zahlenschloss für den Homematic Türschließer

## Statuscodes
- PIN=w => PIN falsch (wrong)
- PIN=o => PIN richtig - Tür geöffnet (open)
- PIN=c => PIN richtig - Tür geschlossen (close)

## Webserver
### Requirements
```
sudo apt-get install php5-mcrypt php5-curl
sudo php5enmod mcrypt
sudo service apache2 restart
```

### Cronjob zum Abholen der Pins
Hier ein Beispiel um alle 30 Minuten nach neuen Pins zu gucken:
```
*/30 *	* * *	root	/usr/bin/php /var/www/cron_get_pins.php > /dev/null 2>&1
```
