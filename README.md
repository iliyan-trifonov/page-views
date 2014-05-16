# Page Views by Iliyan Trifonov

### Quickly check your sites, visitors and search engines behaviour without slowing down your server or staying late at night!

## What is it?
This script stores and shows the page views for the day on a single or multiple domains. Shows the page views made
so far since the beginning of the current day at 00:00h(server time), views in the last minute, an average views per
minute and also a chart as a history since the page was loaded.
No difference between different types of visitors is made. It's just your site and how many pages are opened.
Enough to make conclusions or just check if the site's working.

## Tech stuff
This script uses PHP and Memcached to store the number of page views on the current domain using the HTTP_HOST variable
as a key and only increasing the page views value. Memcached CAS() is used for atomicity.
No personal information is taken from the visitor at all, it's just your domain keeping how many times its pages are opened.

## How to use it?
You edit the config/config.php file, probably just changing the memcached server address if it's not running
on the local host.
Then include public/track.php in your own site's index.php and that's all about the tracking.
Until the tracking is running you setup a new webserver configuration to point to this script's public dir and index.php
as index page. An example Nginx configuration follows:

    server {
            server_name pageviews.your-server.com;

            root /home/mydir/www/pageviews/public;
            index index.php;

            #it's better if you protect this page
            auth_basic "Restricted";
            auth_basic_user_file  /home/mydir/.htpasswd_pageviews;

            location ~ \.php$ {
                    fastcgi_pass 127.0.0.1:9000;
                    fastcgi_index index.php;
                    fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
                    include fastcgi_params;
            }
    }

Reload the web server's configuration and go to http(s)://pageviews.your-server.com where the public/index.php file
will be loaded and you will be presented with a similar page:

![Page Views Preview](http://www.iliyan-trifonov.com/pageviews/PageViews_ITrifonov.jpg)

The main Memcached key is connected to the current day and cached for 24h so when your server passes 00:00h
a new key will be used keeping the old one for a day to be used later if needed.
All domains are put in a single array and single memcached key is used so there is a need for atomicity and so the cas()
is used.

The page refreshes its contents every 60 seconds (configurable in defaults.js) by using jQuery's ajax call and receives
a JSON array. It calculates the difference between the old and new values, an average and stores a historical data
using the amazing Highcharts.js

If you refresh the page all of the data will be lost except the one that shows how many page views happenned today.
This and other little things may be changed in future updates.
