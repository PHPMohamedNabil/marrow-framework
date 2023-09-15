MVC architecture
A project Using an MVC Pattern (core mvc) bulid from zero with features like (command line micros,routes,template engine,containers,service provider pattern, mysql db). 

Getstarting with project:

install composer
after download the project folder just install composer required library in command line

composer install
run migration file
to install db scheme run: ``php migrate```

for using a seeded database with example data you can import database file native_api.sql

env file
you can browse .env file to check and configure database connection and web app settings:

APP_NAME=NativeApi
SECRET_KEY=4fe8895cff6b23cd1f49b1c14c34a5a161248d1fba2d55392d3ef7d3d6296811
ENVIROMENT=development

DB=mysql
HOSTNAME=localhost
USERNAME=root
PASSWORD=
DBNAME=native_api

SESSION_LIFE_TIME=1800
SESSION_IDLE_TIME=1000
feal free to edit the above setting to your enviroment settings.

generate app secret key
this secert key important as it is important in hashing data algorithim it is hashes application name and uses it hashing process as secret key.

run command php generate_key

generate key

you will see key generated take it and copy it in as a value of SECERET_KEY in .env file

Routes
routes located in app\routes folder: 1-api.php for api routes 2-web.php for web routes

in our project we working on api routes you can change it as you want:

use App\Core\Route\Router as Route;
use App\Controllers\ProductController;
use App\Controllers\UserController;

Route::middlewares('api',function(){
    
   Route::prefix('api/',function(){
      
      Route::resource(['product'=>ProductController::class]);

      Route::post('/user/register',[UserController::class,'store']);
      Route::get('/users/',[UserController::class,'allUsers'])->middleware('checktoken');
      Route::post('/user/login',[UserController::class,'userAuth']);

      Route::get('/user/profile',[UserController::class,'profile'])->middleware('checktoken');
      
   });



});
App routes list
run command ** php route_list** to see app routes

routelist

App url
your will go to app\config\config_constants.php file : you will see all application constatnt the most imporatant part is SITE_URL

//webiste address and routes
define('ROUTES_WEB',APP.'routes');
define('SITE_URL','http://localhost:8000/');
define('SITE_AD_URL','http://localhost:8000/admin/');
define('VENDOR',ROOT_PATH.'vendor'.DS);
change SUTE_URL constant to your website or localhost url that has a document root in to public folder.

_tcsrf
this a csrf token parameter you have to send it along with any post request (check cookies to get the full csrf token ) see like that: login

or you can go to config/middlewares.php and disable remove csrf middleware from middlewares array open postman or other tool to test your api

finally change directory to public folder from command line and run php -S localhost:8000 (run built in php server)
