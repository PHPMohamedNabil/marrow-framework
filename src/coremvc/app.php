<?php

namespace Core;

use Core\Container\Container;
use Core\Request;
use Core\Response;
use Core\Route\Router;
use Core\View\Style;

class App extends Container{


   public static $container;

   private static $app_inst;

   public  $route;

   public $request;

   public $response;

   public static $app;

   public $session;

   public $style;

   public $_csrftoken;

   public static $config;

   public const APP_START=CoreStart;

   public function __construct(Container $container)
   {     
      
  
        self::$container = $container;
     
      
      $this->request  = new Request();
      $this->response = new Response();
      self::$app      = $this;

      $this->route    = new Router($this->request,$this->response,ROUTES_WEB);
       
    
      $this->style     = new Style(VIEWS,VIEWS.'temp');
      //Ignition::make()->register()->runningInProductionEnvironment(false);
      

   } 

   public function setEnviroment()
   {

   }

   public function configSetup()
   {

   }

   public function enviroment()
   {
       return $this->enviroment;
   }

   public function isEnvProduction()
   {
       return $this->enviroment == 'production';
   }

   public function errorHanlder()
   {

   }

   public function run()
   {    
         $this->route->match();
   }
   


}