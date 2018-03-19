<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

---
title: User Authentication using Laravel's passport
published: false
description: 
tags: #php #laravel #OAuth2 #authentication
---
#First, let's answer the basic question - Whant is User Authentication?
User authentication is a process that allows an application to verify the identity of someone. Each user is required to log in to the system to access an application. The user supplies the username of an account and a password if the account has one (in a secure system, all accounts must either have passwords or be invalidated). If the password is correct, the user is logged in to that account; the user acquires the access rights and privileges of the account.

#Now, What is Laravel Passport?
APIs typically use tokens to authenticate users and do not maintain session state between requests. Laravel makes API authentication a breeze using Laravel Passport, which provides a full [OAuth2](https://oauth.net/2/) server implementation for your Laravel application in a matter of minutes. Passport is built on top of the [League OAuth2 server](https://github.com/thephpleague/oauth2-server) that is maintained by Alex Bilbie.

If a particular user is authenticated, the token that was generated during login will be stored to seamlessly provide API access to the user until the token is explicitly revoked during the logout.

We'll now create a public API endpoint `Login` and a protected API endpoint `Logout` for logging in and out users in a [Laravel](https://laravel.com) application.

###What is a public API endpoint?
A public API endpoint is available for any users of the web. Take `Login` as an example. A login should be available for everyone in order to login into the application.

###What is a protected API endpoint?
A protected API endpoint will only be available for the authenticated users. Take `Logout` as an example. An account can be logged out only by a legitimate user.

#Let's set up the application.
Before installing Laravel, make sure that you have [Apache](https://httpd.apache.org/) up and running with MySql and PHP V7.2.

We'll need [Composer](https://getcomposer.org) to install Laravel in our system. Composer is a tool for dependency management in PHP. It allows you to declare the libraries your project depends on and it will manage (install/update) them for you.
Composer can be either globally installed or locally installed based on your requirement. We'll install it locally now.

Open a suitable directory and run the following command in your terminal - 

`php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"`

`php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"`

`php composer-setup.php`

`php -r "unlink('composer-setup.php');"`

This will create a `composer.phar` file in the directory you've chosen. Now, let's install our Laravel Application using the following command-

`php composer.phar create-project --prefer-dist laravel/laravel laravel-passport`

After installation, create a database and let's name it as `passport` and open the application in your favourite editor. I prefer [Code](https://code.visualstudio.com/) ❤️. Don't forget to install the composer again inside your project if you have installed it locally before.

#Environent Configuration
It is often helpful to have different configuration values based on the environment where the application is running. For example, you may wish to use a different cache driver locally than you do on your production server.

To make this a cinch, Laravel utilizes the [DotEnv PHP library](https://github.com/vlucas/phpdotenv) by Vance Lucas. In a fresh Laravel installation, the root directory of your application will contain a `.env.example` file. If you install Laravel via Composer, this file will automatically be renamed to `.env`. Otherwise, you should rename the file manually.

Now, open the `.env` file and update the following -

`APP_URL=http://localhost` -> `APP_URL=http://localhost/laravel-passport/public`

`DB_DATABASE=homestead` -> `DB_DATABASE=your database name here i.e passport`

`DB_USERNAME=homestead` -> `DB_USERNAME=your db username`

`DB_PASSWORD=secret` -> `DB_PASSWORD=your db password`

#Let's intall Passport
To get started, install Passport via the Composer package manager:

`php composer.phar require laravel/passport`

After install successfully Passport package in our application we need to set their Service Provider. so, open your `config/app.php` file and add following provider in it.

![Imgur](https://i.imgur.com/2KhTSZS.png)

Now, the Passport service provider registers its own database migration directory with the framework, so you should migrate your database after registering the provider. The Passport migrations will create the tables your application needs to store clients and access tokens. 

###Database: Migrations
Migrations are like version control for your database, allowing your team to easily modify and share the application's database schema. Migrations are typically paired with Laravel's schema builder to easily build your application's database schema. Laravel comes with a default `users table` migration. So, we need not write any migration for this application since we'll be using only the email and password for authentication.

To migrate the users and the other passport tables, run the following artisan command:

`php artisan migrate`

![Imgur](https://i.imgur.com/yKmo2Qx.png)

Next, you should run the `passport:install` command. This command will create the encryption keys needed to generate secure access tokens. In addition, the command will create "personal access" and "password grant" clients which will be used to generate access tokens:

`php artisan passport:install`

After running this command, add the `Laravel\Passport\HasApiTokens` trait to your `App\User(Location - app\User.php)` model. This trait will provide a few helper methods to your model which allow you to inspect the authenticated user's token and scopes:

![Imgur](https://i.imgur.com/8mlxf5c.png)

Next, you should call the `Passport::routes` method within the boot method of your `AuthServiceProvider(Location - app\Providers\AuthServiceProvider.php)`. This method will register the routes necessary to issue access tokens and revoke access tokens, clients, and personal access tokens:

![Imgur](https://i.imgur.com/wt89LNw.png)

Finally, in your `config/auth.php` configuration file, you should set the `driver` option of the `api` authentication guard to `passport`. This will instruct your application to use Passport's `TokenGuard` when authenticating incoming API requests:

![Imgur](https://i.imgur.com/DXfBaBw.png)

#Now, let's write a controller for login and logout.

Run `php artisan make:controller AuthenticationController`. This will create a `AuthenticationController.php` file in `app\Http\Controllers`

###Login

The basic logic behind login will be to find and retrieve the record with the help of the `email` value that comes with the request. After retrieval, if the password that came in the request matches the password of the retrieved record - we will generate a token and send it as a response with the 200 status code. If the password mismatch then we'll send the appropriate error message with 422 status code.

If there is no user found with the request email, the same procedure is followed for password mismatch scenario.

![Imgur](https://i.imgur.com/WVgHLqc.png) 

###Logout
The logic for logout is to retrieve the token from the request header. Then we will explicitly revoke the token.

![Imgur](https://i.imgur.com/e0yd7gx.png)

Our final controller will look like the following:

[Imgur](https://i.imgur.com/2N4WqUi.png)

#Routes

All Laravel routes are defined in your route files, which are located in the routes directory. We'll use `api.php` to define our API routes. We'll define two routes, namely - login and logut. Remember, login is a public route and logout is a private route. The route file will now look like the following:

![Imgur](https://i.imgur.com/DwlwAHZ.png)

#Testing our API using Postman
Postman is a platform that supports and enhances API development. A good ADE will streamline the development process, create a single source of truth for an organization's APIs, and enhance collaboration on APIs across the organization.

Before testing, add a record to your users table to test our API. Also, make sure that the `storage` and `bootstrap/cache` directory of the application is writable.

Testing login - `POST http://localhost/laravel-passport/public/api/login`

![Imgur](https://i.imgur.com/I92bb1j.png)

Testing logout - `GET http://localhost/laravel-passport/public/api/logout`

Now, copy the token and set it as header.

![Imgur](https://i.imgur.com/OWvcQyn.png)

Feel free to check out the final [codebase]()
