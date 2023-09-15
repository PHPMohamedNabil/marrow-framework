<?php

namespace Core\Route;

use Core\Route\Router;
use Core\Request;
use Core\Container\Container;
use Core\Response;
use Optimus\Onion\Onion;
use RuntimeException;
use BadMethodCallException;
use OutOfRangeException;

class Dispatcher {

    private        $router;
    private        $request;
    private        $method;
    private        $response;

   public function __construct(Router $router,$method,Request $request,Response $response) {

        $this->router  = $router;
         //  dd($this->router->container);
        $this->request = $request;
        $this->response = $response;
        $this->method = $method;
    }   

   public function handle($route_info)
    {   
       // dd($route_info);
             //dd($route_info);
        if(isset($route_info['bad']))
        {
              //dd($route_info);
            //dd($route_info);
            $bad =$route_info['bad']??null;

             $this->response->sendHeader('Allow:'.strtoupper($bad));
             $this->response->httpResponse(405);
             throw new BadMethodCallException("Bad Method call expecting:{$bad} method");           

        }

        if(config()->get('route_system'))
        {  
          
            return $this->autoDisptachUrl($route_info);
        }
        
        if($route_info ==false)
        {

             $this->response->httpResponse(404);
             return view('errors.error',['message'=>'Page not Found','respond'=>404]);
        }
       // dd($route_info['handler']);
        

      
        if(is_string($route_info['handler']))
        { 
          $this->allBeforeFilter($route_info['pattern']);
            
            $this->dispatchView($route_info);
       
          return   $this->allAfterFilter($route_info['pattern']);

        }
        if($route_info['handler'] instanceof \closure)
        {  
           
          $this->allBeforeFilter($route_info['pattern']);
        
           $this->dispatchCallback($route_info);

          return   $this->allAfterFilter($route_info['pattern']);
        }

        if(is_array($route_info['handler']))
        {  

           // dd($route_info['handler'][0]);
          if(class_exists($route_info['handler'][0]))
          {
              //dd($route_info['params']);
            $controller = $this->router->container->get($route_info['handler'][0]);
              
            $params = isset($route_info['values'])?$route_info['values']:[];

           //  dd($route_info);
            
           
            $this->allBeforeFilter($route_info['pattern']);
            
            $this->dispatchController($controller,$route_info,$params);

          return  $this->allAfterFilter($route_info['pattern']);

            
                        
            
         
          }
          else
          {
             throw new RuntimeException("class {$route_info['handler'][0]} not exist in controller folder check class name in routes list");
          }

        }

       
    }

    private function hasConstructorMiddlewares($controller,$method)
    {
      $controller_middlewares  =  $controller->getControllerMiddlewares();

        if(count($controller_middlewares['middlewares']))
        { 
            if(!in_array($method,$controller_middlewares['excpet']))
            {
                 return $controller_middlewares['middlewares'];
            }
            else
            {
                 return false;
            }

        }
        else
        {
            return false;
        }

    }

    private function dispatchController($controller,$route_info,$params)
    {
       // dd($this->routeMiddlwares($route_info['pattern']));
       $onion                   =  new Onion;
       $request                 = $this->router->request;
       $middlewares             = ($this->hasConstructorMiddlewares($controller,$route_info['handler'][1]))?:$this->routeMiddlwares($route_info['pattern']);

       return $onion->layer($middlewares)->peel($request , function($request) use ($controller,$route_info,$params){

                 if(method_exists($controller,$route_info['handler'][1]))
                 { 
                    $this->router->container->resloveClassMethod($controller,$route_info['handler'][1],$params);
                   // $controller->{$route_info['handler'][1]}(...$params);
                 }
                 else
                 {
                    throw new RuntimeException("class {$route_info['handler'][0]}:method {$route_info['handler'][1]} not exist in controller check method name");
                 }
            });
               
    }

    private function dispatchCallback($route_info)
    {
         $request  = $this->router->request;
         $response = $this->response;
         $router   = $this->router;
         
         $onion   =  new Onion;
            

      return $onion->layer($this->routeMiddlwares($route_info['pattern']))->peel($request , function($request)  use($route_info,$response,$router){

           if(isset($route_info['values']) && isset($route_info['params']))
           {
             return $route_info['handler'](...array_merge($route_info['values'],[$request,$response,$router]));
           }

              $route_info['handler']($request,$response);

            });
               
    }

    private function dispatchView($route_info)
    {
        $request = $this->router->request;
        
        $onion   =  new Onion;

       return $onion->layer($this->routeMiddlwares($route_info['pattern']))->peel($request , function($request) use($route_info){
                   return view($route_info['handler']);
            });
              
    }

    protected function allBeforeFilter($filter_name)
    { 

        if(isset($this->router->filter['before'][$filter_name]))
        {
            foreach($this->router->filter['before'][$filter_name] as $before)
            {

                 if($before instanceof \Closure || is_callable($before))
                 {
                    $before($this->request,$this->response);
                 }
                 else
                 {
                    continue;
                 }
            }
        }

        return null;

    }

   

    protected function allAfterFilter($filter_name)
    {
        if( isset($this->router->filter['after'][$filter_name]))
        {
            foreach($this->router->filter['after'][$filter_name] as $after)
            {
                 if($after instanceof \Closure || is_callable($after))
                 {
                    $after($this->request,$this->response);
                 }
                 else
                 {
                    continue;
                 }
            }
        }

        return null;
    }


    protected function routeMiddlwares($pattern)
    { 
      $route_middleware = require(MIDDLEWARES.'middlewares.php');
      
      $route_middleware = $route_middleware["route"];
      

      $final_middlwares  =[];
       
        if(isset($this->router->middlewares[$pattern]))
        {   

          $explode_middleware = $this->router->middlewares[$pattern];
            foreach($explode_middleware as $middleware_name)
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
            return $final_middlwares;
        }

         return $final_middlwares;

    }


   protected function autoDisptachUrl($route_info)
   {
       $url = $this->request->qString() ?? '/';       

       $url = trim($url,'/');
       $url = explode('/',$url);

       $controller = isset($url[0])?'App\Controllers\\'.ucwords($url[0])."Controller":'';

       $action     = isset($url[1])?$url[1]:'';
             
             unset($url[0],$url[1]);
       $params     = !empty($url)?array_values($url):[];

          // echo $this->controller;
                 
          if(class_exists($controller) && method_exists($controller,$action))
          {
             $controller_ob =$this->router->container->get($controller);
            return $this->router->container->resloveClassMethod($controller_ob,$action,$params);
          }
          else
          {
                 $this->response->httpResponse(404);
             return view('errors.error',['message'=>'Page not Found','respond'=>404]);
          }
              

        //var_dump($url);
   }



}