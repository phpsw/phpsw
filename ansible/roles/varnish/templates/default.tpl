server {
    listen 8080;
    server_name {{ varnish.servername }} www.{{ varnish.servername }};
    root {{ varnish.docroot }};

    add_header Access-Control-Allow-Origin "http://mozilla.github.io";

    location / {
        try_files $uri /app.php$is_args$args;
    }

    location ~ ^/app\.php(/|$) {
        fastcgi_pass   unix:/var/run/php5-fpm.sock;

        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTPS off;

        internal;
    }

    location /rev {
        rewrite ^/rev/[0-9]+/(.*)$ /$1;
    }

    error_log /var/log/varnish/phpsw_error.log;
    access_log /var/log/varnish/phpsw_access.log;
}
