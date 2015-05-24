# zahlenschloss
Zahlenschloss für den Homematic Türschließer

## Webserver
### mcrypt muss installiert und aktiviert sein
```
sudo apt-get install php5-mcrypt

sudo php5enmod mcrypt

sudo service apache2 restart
```

### Cronjob zum Abholen der Pins
Hier ein Beispiel um alle 30 Minuten nach neuen Pins zu gucken:
```
*/30 *	* * *	root	/usr/bin/php /var/www/cron_get_pins.php > /dev/null 2>&1
```
