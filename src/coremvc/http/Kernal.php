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
    
    public function addIgnitionphoto()
    {
        $path   = ROOT_PATH.str_replace('\\',DIRECTORY_SEPARATOR,'vendor\spatie\ignition\resources\views\errorPage.php');
        $string = file_get_contents($path);

        if(!preg_match('#<img#',$string))
        {
          $required_string  = preg_match('#<!-- The noscript representation is for HTTP client like Postman that have JS disabled. -->#',$string,$match);
                  $img= '<img src="'.SITE_URL.'err_pic/nosa.jpg'.'"/>';
    
             $replacer = preg_replace('#'.$match[0].'#',$match[0]."\r\n".$img,$string);
         
           @file_put_contents($path,$replacer);

        }
        return null;

    }
    public function ignition($env=false)
    {
       Ignition::make()->register()->runningInProductionEnvironment(false);
    }


    public function setINIAndAppOptions()
    {  
        error_reporting(0);
        ini_set('display_errors', 0);
        ini_set('display_errors','off');
        ini_set('display_startup_errors','off');
    }

    public function handle(Request $request,App $app)
    {  
        $onion =  new Onion;
        
        $onion->layer($this->middlewares())->peel($request, function($request){
                return $request;
            });

        return $app->run();
    }

    public function setTimeZone()
    {
        date_default_timezone_set(config()->get('date_default_timezone_set'));
    }

    public function middlewares()
    {
       $middlewares=require_once(MIDDLEWARES.'middlewares.php');
        
        $arr = [];

        foreach ($middlewares['web'] as $key => $value)
        {
             $arr[]=new $value;
        }

        return $arr;

    }
    

    public function enviroment()
    {
      
       if(env('ENVIROMENT') =='development')
       {    
            $this->addIgnitionphoto();
            $this->ignition(false);
       }
       elseif(env('ENVIROMENT') =='production')
       { 
          $this->ignition(true);
          $this->setINIAndAppOptions();
       }
       elseif(env('ENVIROMENT') =='maintenance')
       {    
          app()->response->httpResponse(503);

           return exit('<center><h1>Maintenance: Sorry for that as we are working now to enhance our site to bring a high quality service.</h1></center>');
       }
    }

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



}