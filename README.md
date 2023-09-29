# Marrow MVC architecture
![marrow](https://github.com/PHPMohamedNabil/marrow-framework/assets/29188634/7bdc6061-86e4-4623-9f48-f6a2862e0256)

A php framework Using  MVC design pattern build from zero with features like (command line micros,routes,template engine,containers,service provider pattern, mysql db,middlewares), help ypu understand poupler frameworks and how it works and operating from inside.
get started with new project:

see the below repo to create new skeleton project.
https://github.com/PHPMohamedNabil/marrow

# **This the first version  and it is under testing**

## install composer
after download the project folder just install composer required library in command line

```php 
composer create-project php-mohamed-nabil/marrow --prefer-dist myapp
```

# Request lifecycle

all request to web applications directed to public/index.php file that acts as a front controller for all web application requests

```php
<?php

define('CoreStart',microtime());

use Core\Request;
use Core\Response;
use Core\http\Kernal;

require('autoload.php');

$app= require_once __DIR__.'/../bootstrap'.DS.'bootstrap.php';

$kernal = new Kernal;

$kernal->lightOn();

$kernal->handle(new Request,$app);

$kernal->lightOff();

```
First thing is creating a application new instance and then run required classes or servicess (startups under startups folders) using kernal class
to handel application request and then retrun response to the client.

## kernal file 

kernal file it is like a motherboard that conducts,configuraing and preparing all application settings and runs app services : 
check kenral file core/http/kernal.php:
```php
<?php

namespace Core\Http;

use Core\Request;
use Optimus\Onion\Onion;
use Core\App;
use Core\Lightes\LightesFaced;
use Core\Lightes\Lightes;
use Dotenv\Dotenv;
use Core\Session\Storage\SessionStorage;
use Core\Session\SessionFactory;
use Spatie\Ignition\Ignition;
use Core\Configs\Config;

class Kernal{


    protected $app_middlewares=[];

    public $lightes;

    public function __construct()
    {   
       $dot_env = Dotenv::createMutable(ROOT_PATH);
       $dot_env->load();
         
       app()::$config      = Config::getInstance();

       app()->session      = SessionFactory::create(SessionStorage::class,require_once(SESSION_CONFIG));

       $light_start        = new Lightes(require_once(CONFIG_CONSTAN.'startups.php'));
        
       $this->lightes      = new LightesFaced($light_start);
 
       app()->_csrftoken   = session_token();  

          config()->load(CONFIG.DS.'app.php');
    }

```

# lightes component

This class implements design pattern you can choose or implement all services runs when application requests starts and run services after response returned:
check core/lightes:

```php
<?php

namespace Core\Lightes;
use Core\Lightes\LightesInterface;


class Lightes implements LightesInterface{
     
    private  $service_rooms;

	public function __construct(array $rooms)
	{
        $this->setRoom($rooms);
	}

	public function setRoom($rooms)
	{

           $this->service_rooms=$rooms;
	}

	public function on()
	{ 
		
		foreach($this->service_rooms as $service)
		{
			   $service =  app()::$container->get($service);
              
			   app()::$container->resloveClassMethod($service,'register');
			   app()::$container->resloveClassMethod($service,'startup');
			  
		}

	}

	


	public function off()
	{
        
	}


}
```
use lightes interface for creating your own facede class implementation :
```php
<?php

namespace Core\Lightes;


interface LightesInterface{

       public function on();

       public function off();
}
```

# Kernal from inside

see kernal handel function takes two parameters first is request object and seconde is application instance:</br>

runs middlewares before and afer application requests and then routes the request to the resource:
```php
public function handle(Request $request,App $app)
    {  
        $onion =  new Onion;
        
        $onion->layer($this->middlewares())->peel($request, function($request){
                return $request;
            });

        return $app->run();
    }
```
### kernal light on and light off methods:
you can see this methods runs every thing before request and after response outputed:

```php

    public function lightOn()
    {  
       $this->enviroment();
       $this->lightes->turnOn();
       $this->setTimeZone();
    }

    public function lightOff()
    {   
        //after output services here
         return $this->lightes->turnOff();
    }

```


## migration commands
to install db scheme run:
``` php migrate ``` **run all migrations**
``` php create_migration (migration_name) ``` **create new migration file in migration folder**
``` php migrate role=(all)```  **rollback all migrations**
``` php migrate role=(migration_name)``` **rollback migration_name file**

## controllers and models commands

``` php create_controller (controllername) ``` **create new controller file under controllers folder** <br />

``` php create_controller (controlername) resource ```  **create new resource controller under controllers folder** <br />

``` php create_controller (controlername) resource model ``` **create new resource controller and model file under controllers folder and model folder** <br />

``` php create_controller (controlername) model ``` **create new controller and model file under controllers folder and model folder** <br />

``` php create_model (modelname) ``` **create new model file under models folder** <br />

``` php create_repo (repositoryname) ``` **create new respository file under respositories folder** <br />

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
## routes placeholders
create route placeholders just but : before the placholder:
```php

use App\Core\Route\Router as Route;
use App\Controllers\ProductController;
use App\Controllers\UserController;

Route::get('/user/:id',[UserController::class,'profile']);
```
### routes regx route
make regex routes with method regx just write your own regular expressions (without regx delemeters):

```php

use App\Core\Route\Router as Route;
use App\Controllers\ProductController;
use App\Controllers\UserController;

Route::get('/user/:id',[UserController::class,'profile'])->regx('(\d+)$');
```
## App routes list 
run command ** php route_list** to see app routes

![routelist](https://github.com/PHPMohamedNabil/PHP-Navtive-JWT-API/assets/29188634/5fd13226-1a22-4745-9a0d-12caddfee243)

## Style template engine
marrow uses style template engine it is an fast and powerfull php template engine build from native code with strong featues like (template inheritance,template sections and hard compile feature)
see style documentation here :[style](https://github.com/PHPMohamedNabil/Style) 

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


finally run php marrow to start your project on localhost:8000 or ex: run php marrow (port number) php marrow 4500 
