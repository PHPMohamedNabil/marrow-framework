# Marrow MVC architecture

A project Using a MVC Pattern  bulid from zero with features like (command line micros,routes,template engine,containers,service provider pattern, mysql db,middlewares).

Getstarting with new project:
see the below repo to create new skeleton project.
https://github.com/PHPMohamedNabil/core

## install composer
after download the project folder just install composer required library in command line

```php 
composer require php-mohamed-nabil\core
```
## migration commands
to install db scheme run:
``` php migrate ``` **run all migrations**
``` php create_migration (migration_name) ``` **create new migration file in migration folder**
``` php migrate role=(all)```  **rollback all migrations**
``` php migrate role=(migration_name)``` **rollback migration_name file**

## controllers and models commands
to install db scheme run:
``` php create_controller (controllername) ``` **create new controller file under controllers folder**
-----
``` php create_controller (controlername) resource ```  **create new resource controller under controllers folder**
-----
``` php create_controller (controlername) resource model ``` **create new resource controller and model file under controllers folder and model folder**
-----
``` php create_controller (controlername) model ``` **create new controller and model file under controllers folder and model folder**
-----
``` php create_model (modelname) ``` **create new model file under models folder**
-----
``` php create_repo (repositoryname) ``` **create new respository file under respositories folder**

# env file

you can browse .env file to check and configure database connection and web app settings and session settings:

```
APP_NAME=MyFIRSTAPP
SECRET_KEY=4fe8895cff6b23cd1f49b1c14c34a5a161248d1fba2d55392d3ef7d3d6296811
ENVIROMENT=development

DB=mysql
HOSTNAME=localhost
USERNAME=root
PASSWORD=
DBNAME=native_api

SESSION_LIFE_TIME=1800
SESSION_IDLE_TIME=1000
```
feal free to edit the above setting to your enviroment settings.

## generate app secret key
this secert key important as it is important in hashing data algorithim it is hashes application name and uses it hashing process as secret key.

**run command php generate_key** 

![generate key](https://github.com/PHPMohamedNabil/PHP-Navtive-JWT-API/assets/29188634/d0bfe349-d6a7-4030-977c-674f5f5b613f)

you will see key generated take it and copy it in  as a value of SECERET_KEY in .env file

# Routes 

**Supporting GET,POST,HEAD,PUT,DELETE,OPTIONS**

routes located in app\routes folder:
1-web.php for web routes

in our project we working on  routes you can change it as you want:

```php
use App\Core\Route\Router as Route;
use App\Controllers\ProductController;
use App\Controllers\UserController;

Route::get('/',function(){
   return view('home');
});

Route::middlewares('api',function(){
    
   Route::prefix('api/',function(){
      
      Route::resource(['product'=>ProductController::class]);

      Route::post('/user/register',[UserController::class,'store']);
      Route::get('/users/',[UserController::class,'allUsers'])->middleware('checktoken');
      Route::post('/user/login',[UserController::class,'userAuth']);

      Route::get('/user/profile',[UserController::class,'profile'])->middleware('checktoken');
      
   });



});
```
## App routes list 
run command ** php route_list** to see app routes

![routelist](https://github.com/PHPMohamedNabil/PHP-Navtive-JWT-API/assets/29188634/5fd13226-1a22-4745-9a0d-12caddfee243)

## App url 
your will go to app\config\config_constants.php file :
you will see all application constatnt the most imporatant part is SITE_URL

```php
//webiste address and routes
define('ROUTES_WEB',APP.'routes');
define('SITE_URL','http://localhost:8000/');
define('SITE_AD_URL','http://localhost:8000/admin/');
define('VENDOR',ROOT_PATH.'vendor'.DS);
```
change SUTE_URL constant to your website or localhost url that has a document root in to public folder.

# _tcsrf
this a csrf token parameter you have to send it along with any post request (check cookies to get the full csrf token ) see like that:

# Middlwares 

create application general middlwares or routes middlewares from config/middlewares.php and disable remove csrf middleware from middlewares array.

```php
<?php
use App\Middlewares\csrf;
use App\Middlewares\PostSize;
use App\Middlewares\test;
use App\Middlewares\XcsrfCookie;
use App\Middlewares\ViewValidationError;
use App\Middlewares\ApiMiddlware;
use App\Middlewares\isLogedIn;

return[
        //startup application middleware here
      'web'=>[
               ViewValidationError::class,
               csrf::class,
               XcsrfCookie::class,
           
        ],
        //routes middlewares here example: ['middleware_name'=>'middleware']
      'route'=>[
           'test'        => test::class,
           'api'         => ApiMiddlware::class,
           'checktoken'  => isLogedIn::class
      ]
];
```

# service provider

create application service provider (classess and servicess runs before application bootstraped).
this classes you can create under startup folder and assign it to startups array in app\config\startup.php.

## create startup
go to startup folder and create new file example : 
TimeZoneStartup.php for setting default timezone before app startsup (before every request to application)
**like service providers in laravel framwork**

```php
<?php

namespace App\Startups;

use App\Repositories\BookInterface;
use App\Repositories\BookRepository;
use App\Core\Container\Container;
use App\Startups\StartupInterface;
use App\Core\Database\NativeDB;
use App\Core\Request;
class TimeZoneStartup implements StartupInterface
{
   //timezone service provider if you are saving timezone in db
   //and wants to change it before application startup
      
      public function startup()
      {  
        config()->set('date_default_timezone_set','Europe/Moscow');

        //example:'Europe/Moscow'     
      }

      public function register()
      {   
         
      }

      
      
}

```
This means that every request to application will set date_default_timezone_set to 'Europe/Moscow' 
or you can write your other method to change timzone like date_default_timezone_set() builtin php function.

**all startups are run one by one instantiating every class and run (boot) startup method then run register method**

```php
<?php


use App\Startups\ProductStartup;
use App\Startups\TimeZoneStartup;

return[

     ProductStartup::class,
     TimeZoneStartup::class

];

```


The concept of large framworks (middlewares,pipeline,repositories,commands,migrations,containers,configs,template-engine)

# Exceptions:
![core_exception](https://github.com/PHPMohamedNabil/Core-MVC-PHP/assets/29188634/7e2ef83e-c961-4a09-9744-c3f059b507ec)


finally change directory to public folder from command line and run php -S localhost:8000 (run built in php server)  
