# Tabloid

Agile CMS for building your own Quora, Reddit, Stackexchange, HackerNews or Medium clone. 

Whatever community website would you like to launch? Tabloid is the answer!

## Ultra-concise installation instructions:

1. Create a MySQL database
2. Create a MySQL user with full permissions for that database
3. Execute install.sql on our fresh database
4. Rename env.sample.php to env.php and edit the env vars
5. Place all the Tabloid files on your server
6. Create folders /upload and /cache inside of /public with 777 permissions
7. In case of Nginx, use config below. In case of Apache - there are .htaccess already
8. Open your newly deployed Tabloid and enjoy :)

More detailed installation instructions here: http://www.tabloid.dev/

## Nginx Config

```
server {

    server_name yourdomain.tld;
    listen 80;
    listen [::]:80;

    root /var/www/yourdomain.tld/public;
    index index.php;

    location / {

        gzip on;
        gzip_disable "msie6";
        gzip_types text/plain text/css application/json application/x-javascript text/xml application/xml application/xml+rss text/javascript application/javascript;

        sendfile           on;
        sendfile_max_chunk 1m;
        tcp_nopush on;

        if (!-f $request_filename) {
            rewrite ^/(.+)?$ /index.php?rewrite=$1 last;
        }

        try_files $uri $uri/ =404;

    }

    # Speed Cache for Media: images, icons, video, audio, HTC
    location ~* \.(?:css|js|jpg|jpeg|gif|png|ico|cur|gz|svg|svgz|mp4|ogg|ogv|webm|htc)$ {
        expires 1d;
        access_log off;
        add_header Cache-Control "public";
    }

    location /robots.txt {
        allow all;
        access_log off;
        log_not_found off;
        return 200 "User-agent: *\nSitemap: http://$server_name/sitemap.xml\nDisallow: /admin/\nDisallow: /users\nDisallow: /user/\nDisallow: /tags\nDisallow: /tag/\nDisallow: /cache/\nDisallow: /upload/";
    }

    location ~ \.php$ {

        fastcgi_split_path_info ^(.+?\.php)(/.*)$;
        try_files $fastcgi_script_name =404;
        set $path_info $fastcgi_path_info;
        fastcgi_param PATH_INFO $path_info;
        fastcgi_index index.php;
        include fastcgi.conf;

        fastcgi_pass unix:/var/run/php/php7.3-fpm.sock;
    }

    location ~ /\. {
        access_log off;
        log_not_found off;
        deny all;
    }

}
```
