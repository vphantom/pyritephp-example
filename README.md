# PyritePHP

PHP/Bootstrap framework to kick-start multilingual web application development

Simple event-driven framework for creating PHP 5 applications backed by a PDO database and with a Twitter Bootstrap user interface.  Emphasis has been given on security:

* SQL queries use placeholders, of course, and a whitelist for column names;
* User passwords are saved in cryptographically secure hash form in the database;
* Twig templating has escaping enabled globally by default;
* Sessions are tied to the browser's IP address and fingerprint to reduce the risk of hijacking;
* Form displays are tied to the current session to elimiate duplicate submissions and further reduce the risks associated with session hijacking and scripted attacks;
* New users require e-mail confirmation to become active;
* E-mail and password changes require password re-entry and trigger e-mail notifications;
* Covering 98% of users, forms are validated client-side to improve responsiveness.

Just use this repo as your starting point, modify this file and the contents of `modules/` and `templates/` to suit your needs.  The rest of the developer documentation can be found in [Developers](DEVELOPERS.md).

### Why the name "Pyrite"?

This framework was actually built as the starting point for a commercial project named "PyriteView" as a play on the words "Peer Review".  The framework was then forked on its own as it is highly generic.  The name "PyritePHP" then made sense, considering its origin.


## CURRENTLY UNDER DEVELOPMENT!

As of November 2016, PyritePHP is under active development and has not yet achieved its "v1.0.0" milestone.  You probably want to wait for that initial full-featured release before using or forking it.


## Installation

### Requirements

* PHP 5.x or later
* PHP's `mcrypt` extension module
* PHP's `pdo_sqlite` extension module
* SQLite 3
* Typical Linux command line tools: wget, gzip
* A web server of course

### Web Server Configuration

In order to produce clean, technology-agnostic URLs such as `http://www.yourdomain.com/articles/127`, you need to tell your web server to internally redirect requests for non-existent files to `/index.php`, which will look in `PATH_INFO` for details.  We also want to prevent access to private files.

Here are sample configurations for major server software:

#### Apache

```
RewriteEngine on

RewriteRule ^(bin|lib|modules|node_modules|templates|var|vendor) - [F,L,NC]

RewriteRule ^$ /index.php [L]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+) /index.php/$1 [L]
```

#### Nginx

```
location ~ /(bin|lib|modules|node_modules|templates|var|vendor) {
    deny all;
    return 404;
}

location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_param SCRIPT_FILENAME $document_root $fastcgi_script_name;
    include fastcgi_params;
}

location / {
    index index.html index.htm index.php;
    try_files $uri $uri/ $uri/index.php /index.php;
}
```

#### Lighttpd

```
# TODO: Deny private directories
url.rewrite-if-not-file (
    "^/(.*)$" => "/index.php/$1"
)
```

### First-time initialization

Clone or unzip this repository into the document root of the web site this will become.

Edit `config.ini` to change any defaults as needed.

Run `make init`.  This will automatically download and set up PHP's Composer package manager, then use it to download runtime dependencies locally.  Finally, it will create the database tables and the administrative user so you can log into your new installation.  You will be prompted on the command line for an e-mail address and password to use for that unrestricted account.  (**NOTE:** This prompt requires PHP's `readline`, so *it will not work on Windows*.)

You will also need to make sure that your web server or PHP process has read-write access to the `var/` directory where the database, logs and template cache are stored.


## MIT License

Copyright (c) 2016 Stephane Lavergne <https://github.com/vphantom>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
