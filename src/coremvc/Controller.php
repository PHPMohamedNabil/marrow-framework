<?php


namespace Core;

use Core\RegisteryPattern;
use Optimus\Onion\Onion;
use InvalidArgumentException;
use BadMethodCallException;

abstract class Controller extends RegisteryPattern
{
	private $middlewares=[];

	private $options;

	protected $app;

	protected $request;

	protected $session;

	protected $excpet=[];
	
	protected $response;
	
	public function __construct()
	{
	  $this->app      = app();
	  $this->request  = $this->app->request;
	  $this->response = $this->app->response;
	  $this->session  = $this->app->session;
	}

	protected function middleware(array $names,array $excpet=[])
	{ 
		//dd(123);
		 $this->excpet=$excpet;

		 $route_middleware = require(MIDDLEWARES.'middlewares.php');
		 $route_middleware = $route_middleware["route"];
         $final_middlwares = [];
       
		foreach($names as $middleware_name)
        {
              if(isset($route_middleware[$middleware_name]))
              {
                $final_middlwares[]=new $route_middleware[$middleware_name];
              }
              else
              {
                 throw new OutOfRangeException("Middleware $middleware_name but not found in the application routes middlewares array check middleware name to route($pattern)",);
                 
              }

                 
        }
           $this->middlewares=$final_middlwares;

        return $final_middlwares;
	}

	public function getControllerMiddlewares()
	{
		return ['middlewares'=>$this->middlewares,'excpet'=>$this->excpet];
	}

	
	public function __call($method,$argument)
	{  

		   throw new BadMethodCallException("method {$method} not exist in the controller class");
		
	}

}