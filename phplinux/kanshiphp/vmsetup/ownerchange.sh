#!/bin/bash
chown -R apache:apache /var/www/html/kanshiphp
chown -R apache:apache /var/www/html/mrtg
chown -R apache:apache /var/www/html/mrtg
chown -R apache:apache /var/www/html/plot
rm -rf /var/www/html/httplogs
mkdir /var/www/html/httplogs
chown -R apache:apache /var/www/html/httplogs

