find /var/www/html/uklogin -type d -exec chmod 0755 {} \;
find /var/www/html/uklogin -type f -exec chmod 0644 {} \;
find /var/www/html/uklogin/work -type d -exec chmod 0755 {} \;
find /var/www/html/uklogin/log -type d -exec chmod 0755 {} \;
chmod -R 7777 /var/www/html/uklogin/work
chmod -R 7777 /var/www/html/uklogin/log
chmod 0777 /var/www/html/uklogin/tools/secure.sh
chmod 0777 /var/www/html/uklogin/tools/unsecure.sh


