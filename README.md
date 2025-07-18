# Doorbell Web

A simple doorbell sound trigger web service.

This is for browser to monitor a virtual doorbell that can be potentially
triggered by other web services or client application.


## Usage

Simply host the application's "/public" folder with proper Nginx / Apache
with php setup.


## API

Provides 2 API endpoints:

* /api/trigger
  A doorbell trigger to trigger the doorbell effect.
  Requires an API key in Authorization header in the format of:
  ```
  Authorization: Basic <api key>
  ```

* /api/sse
  A listener to listen for doorbell trigger (or potentially other topics).
  Requires no authorization.


## Slim Framework 4 Skeleton Application

[![Coverage Status](https://coveralls.io/repos/github/slimphp/Slim-Skeleton/badge.svg?branch=master)](https://coveralls.io/github/slimphp/Slim-Skeleton?branch=master)

This is built using the slim 4 skeleton applicaiton.

Use this skeleton application to quickly setup and start working on a new Slim Framework 4 application. This application uses the latest Slim 4 with Slim PSR-7 implementation and PHP-DI container implementation. It also uses the Monolog logger.

This skeleton application was built for Composer. This makes setting up a new Slim Framework application quick and easy.


### Install the Application

Run this command from the directory in which you want to install your new Slim Framework application. You will require PHP 7.4 or newer.

```bash
composer create-project slim/slim-skeleton [my-app-name]
```

Replace `[my-app-name]` with the desired directory name for your new application. You'll want to:

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `logs/` is web writable.

To run the application in development, you can run these commands 

```bash
cd [my-app-name]
composer start
```

Or you can use `docker-compose` to run the app with `docker`, so you can run these commands:
```bash
cd [my-app-name]
docker-compose up -d
```
After that, open `http://localhost:8080` in your browser.

Run this command in the application directory to run the test suite

```bash
composer test
```

That's it! Now go build something cool.


## License

This software is licensed under the MIT License. A copy of the license
is provided in (LICENSE.md)[LICENSE.md] of this folder.
