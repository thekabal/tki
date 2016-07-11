# The Kabal Invasion

## REQUIREMENTS
See the `/README.md` file.

## INSTALLATION
1. Download the [latest stable release](https://github.com/thekabal/tki/releases).
2. Unzip (or untar `tar -xvf tki-x.x.x.tar`) the release.
3. `chmod 777` the directories `/templates/_cache` and  `/templates/_compile`. 
   This allows the Smarty template system to write its cached and compiled versions 
   of templates.
4. Open the file `{{hostname}}/setup_info.php` in your browser. This file 
   will help you understand what settings you should use in your 
   `config/SecureConfig.php` file.
5. Edit the `/config/SecureConfig.php` files to your own settings and manually update 
   your cron file. 
6. PLEASE edit `game_name` in `/config/classic_set_config.ini.php` to be something 
   unique!
7. Contact your hosting provider, and determine which command-line web fetching
   program they make available to you, and where it is located. This is to help
   set up a background task that will be called every `x` minutes. You can set
   this to any interval you want, but 5 or 6 minutes are good standard values.
   This task needs to call the web page `scheduler.php` passing the admin
   password to it e.g `http://{{host}}/scheduler.php?swordfish=your-password`.
   On UNIX and Linux, you can achieve this by using cron to call lynx to access
   the pages at specified times. Lynx is just one of many programs you can use
   to access the page. It may not be available on your server, and you will need
   to substitute a different program. Please check your hosting provider
   documentation to determine which program is available.

   - A sample crontab follows:

     `*/6 * * * * /usr/bin/lynx --dump http://{{host}}scheduler.php?swordfish=password > /dev/null`

   - A few alternatives:

     `*/6 * * * * /usr/bin/GET http://localhost/tki/scheduler.php?swordfish=password > /dev/null`

     `*/6 * * * * /usr/bin/wget "http://localhost/tki/scheduler.php?swordfish=password" > /dev/null`

   - Please note that your hosting provider may have these programs at a
     different location than /usr/bin, requiring you to change the location to
     match their documentation.
   - You will need to alter the URL to point to your exact domain name.
   - And path. You will also need to change the password to your admin password.
   - To specify how fast-paced you want the game to be, you will need to
     edit the file config/SecureConfig.php and select how many minutes you want
     between different events, e.g turns or port regeneration.

8. Create the database: `mysqladmin -uuser -ppass create dbname`.
9. Visit the page `http://{{hostname}}/create_universe.php` in your browser.  
   You'll need to enter your admin password to access this page.  Change the
   settings to suit the universe you'd like to create, and go for it.
10. Open the file `http://{{hostname}}/index.php` in your browser; you should now
   be able to log-in.
11. `chmod 000 setup_info.php` - it contains sensitive information.
12. If you'd like additional security, we have included `.htaccess` files for some
   protection. Some systems do not ship with `.htaccess` enabled. You'll need to
   edit your Apache config (either `httpd.conf` or the correct file for your
   host/directory), and set it to `AllowOverrides Limits` (it is often set to
   `AllowOverrides None`). Don't forget to reload Apache's config if you make
   this change.
13. Hopefully it works now :smile:

### ADDITIONAL FEDORA/REDHAT NOTES
- To be able to sendmail from Apache, you'll need to set:
  `setsebool -P httpd_can_sendmail=1`.
- To allow the web server to talk to the MySQL server. Issue this command:
  `setsebool -P httpd_can_network_connect_db 1`.
- To ensure safety, you need to enable htaccess processing in your web server.
  For apache, this is AllowOverrides Limit or AllowOverrides All in the server config file (httpd.conf / apache2.conf)

**Join the TKI forums to stay informed of updates, patches and new releases.**

## BUG REPORTS AND SUPPORT
Bug reports, patches, and requests for support should be submitted on the GitHub 
project for TKI at <https://github.com/thekabal/tki>.
