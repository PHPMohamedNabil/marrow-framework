<?php

namespace Core\Route;

use Core\Request;
use Core\App;
use Core\Response;
use Core\Route\Dispatcher;
use OutOfRangeException;
use RuntimeException;
use BadMethodCallException;

class Router {
     

    protected  ?array $routes = [];

    private    array $valid_methods =[
        'get',
        'post',
        'delete',
        'put',
        'patch',
        'head',
        'options'
    ];


     public $request;

     public array $filter =[
          'before'=>[],
          'after'=>[]
     ];

     public array $names=[];

     public $current_path=null;

     protected $cotainer;

     public  array $middlewares=[];

     public  ?string $prefix ='';

     public  ?string   $namespaces='';

     public  $current_middleware=null;

     public array $regx = [];

    public function __construct(Request $request=null,Response $response=null,$routes=null)
    { 

      if($request && $response)
      {
         $this->request =  $request;
         $this->response =  $response;
         $this->container = App::$container;
      }
      else
      {
         $this->request =  new Request();
         $this->response =  new Response();
         $this->container = App::$container;

      }
        
         if($routes)
         { 
            //die('asdas');
            $router = $this;
                        
            require($routes.'/web.php');
             
         }


    }

    private function addRoute($method,$path,$callback)
    {    
        $path = rtrim('/'.ltrim($this->prefix,'/'),'/').'/'.trim($path,'/');
    
    
  
        if(!in_array($method,$this->valid_methods))
        {
          throw new RuntimeException('Method you register for route is not supported or not valid please check valid methods in '.__CLASS__);
        }

         if(is_array($callback))
         {   
            $classname=$this->namespaces.trim($callback[0],'\\');

            $callback=[$classname,$callback[1]];

           
         }

          $this->routes[]=[
                           'method'=>$method,
                           'handler'=>$callback,
                           'pattern'=>$path
                          ]; 

         $this->current_path = $path;

         if($this->current_middleware)
         {
             $this->middleware($this->current_middleware);
         }

    }

    public function get($path,$callback)
    {
        $this->addRoute('get',$path,$callback);
        $this->addRoute('head',$path,$callback);
        return $this;
    }

     public function post($path,$callback)
    {
        $this->addRoute('post',$path,$callback);
        return $this; 

    }

     public function put($path,$callback)
    {
        $this->addRoute('put',$path,$callback);
        return $this; 

    }

    public function delete($path,$callback)
    {
         $this->addRoute('delete',$path,$callback);
        return $this;  

    }

    public function patch($path,$callback)
    {
       $this->addRoute('patch',$path,$callback);
        return $this;   

    }

    public function head($path,$callback)
    {
        $this->addRoute('head',$path,$callback);
        return $this;  

    }

    public function options($path,$callback)
    {
        $this->addRoute('options',$path,$callback);
        
        return $this;  

    }

    public function any($path,$callback)
    {  
           $this->addRoute('get',$path,$callback);
           $this->addRoute('head',$path,$callback);
           $this->addRoute('post',$path,$callback);
           $this->addRoute('put',$path,$callback);
           $this->addRoute('delete',$path,$callback);
           $this->addRoute('options',$path,$callback);
           $this->addRoute('patch',$path,$callback);

          return $this;  
    }

    public function resource(array $resources)
    {
          if(is_array($resources) && count($resources))
          {
            foreach($resources as $resource=>$class)
            {

             $this->get('/'.$resource,[$class,'index'])->name($resource.'.index');
             $this->get('/'.$resource.'/create',[$class,'create'])->name($resource.'.create');
             $this->get('/'.$resource.'/:id',[$class,'show'])->name($resource.'.show');
             $this->get($resource.'/:id/edit/',[$class,'edit'])->name($resource.'.edit');
             $this->post($resource.'/store/',[$class,'store'])->name($resource.'.store');
             $this->put($resource.'/:id/update/',[$class,'update'])->name($resource.'.update');
             $this->delete($resource.'/:id/delete/',[$class,'destroy'])->name($resource.'.delete');

            }

             return $this;

          }

          return false;
    }


    public function prefix($prefix,callable $callback)
    {
          $base_prefix = $this->prefix;
         
         $this->prefix .= $prefix;
          
         // dd($namespace);
         $callback($this);
         
         $this->prefix =  $base_prefix;
    }

    public function groupNamespace($namespaces,callable $callback)
    {
         $base_namespace = $this->namespaces;
         
         $this->namespaces .= $namespaces;
          
         // dd($namespace);
         $callback($this);
         
         $this->namespaces =  $base_namespace;
    }

    public function middlewares($middlewares,callable $callback)
    {   

        $this->current_middleware = $middlewares; 
          
        
        $callback($this);

      $this->current_middleware=''; 

    }

    public function middleware($middleware)
    { 
        if(is_array($middleware))
        {
           $this->middlewares[$this->current_path]=$middleware;         
             return $this;
        }
         if(isset($this->middlewares[$this->current_path]) && !in_array($middleware,$this->middlewares[$this->current_path]))
        {
          array_push( $this->middlewares[$this->current_path],$middleware);
        }
        elseif(isset($this->middlewares[$this->current_path]) && in_array($middleware,$this->middlewares[$this->current_path]))
        {
            return $this;
        }
        else
        {
            $this->middlewares[$this->current_path]=[$middleware]; 
        }   

        return $this;
 
   
    }

    public function filter($pos,callable $callback)
    {   
        if(!isset($this->filter[$pos]))
        {
            throw new OutOfRangeException("Error Processing filter of route with pattern $this->current_path please choose either before or after");
            
        }
       
       if(isset($this->filter[$pos][$this->current_path]))
       {
          array_push( $this->filter[$pos][$this->current_path],$callback);
       }
       else
       {
          $this->filter[$pos][$this->current_path]=[$callback];
       }
       
         
         return $this;
    }

    public function assignFilter($pos,$pattern,callable $callback)
    {   
        if(!isset($this->filter[$pos]))
        {
            throw new OutOfRangeException("Error Processing filter of route with pattern $this->current_path please choose either before or after");
            
        }
       
       if(isset($this->filter[$pos][$pattern]))
       {
          array_push( $this->filter[$pos][$pattern],$callback);
       }
       else
       {
          $this->filter[$pos][$pattern]=[$callback];
       }
       
         
    }

    public function name($route_name)
    {   
         $this->names[$route_name]=$this->current_path;

          // return $this->names[end($this->names)];
         return $this;
    }

    protected function matchPattern($pattern)
    {
        if(preg_match_all('#\:([\w]+)#',$pattern,$match))
        {
            $pattern_comp = preg_replace('#\:([\w]+)#','([\w]+)',$pattern); 
          if(preg_match('#'.$pattern_comp.'#',$this->request->getPath(),$match2))
          {   
            if(count(explode('/',$pattern_comp)) == count(explode('/',$this->request->getPath())))
            {
                return [$match2[1]];
            }

               return false;    
          }
           return false;

        }

        return false;

    }

    public function getRouteName($route_name,array $params=null)
    { 
        $params= (is_array($params))?$params:[];

         if(isset($this->names[$route_name]))
         {

           if(preg_match_all('#\:([\w]+)#',$this->names[$route_name],$match))
           {   
               $reslove_route =''; 

              if(!$params)
              {
                throw new RuntimeException("you must enter route params to the route named $route_name");           
              }
              
              if(count($match[0]) != count($params))
              {
                    $count = count($params);
                    $count2 =count($match[0]);

                   throw new RuntimeException("missing route parameters for route $route_name $count entered expecting $count2");
              }
                   
                   $reslove_route = str_ireplace($match[0],$params,$this->names[$route_name]);
                
                    return $reslove_route;
           }
           else
           {
                return $this->names[$route_name];

           }


         }

    }



   public function match()
   {  

        $path       = $this->request->getPath();
        $method     = $this->request->getMethod();
        $route_info = $this->resolve($method,$path);
          
      //   return dd($this->all('get'));

        $dispatch = new Dispatcher($this,$method,$this->request,$this->response);
 
         return $dispatch->handle($route_info);      

   }

   public function regx($regx)
   {   
      $current_path=$this->current_path;
      
     
        if(isset($this->regx[$current_path]))
        {
      
           return $this;
        }
        else
        {
            $this->regx[$current_path]=$regx; 

             return $this;
        }

        
   }

   protected function matchRegxRouteAfterResolve($pattern,$resolved_ptr,$check_parts=null)
   {  
         
        // dd($this->regx[$pattern]);
      if($check_parts)
      {   
         $pattern         = ($pattern)?$pattern:'/';
         $resolved_ptr    = ($resolved_ptr)?$resolved_ptr:'/';
          
         // dd($resolved_ptr);
          $pattern_exp      = ($pattern=='/')?['/']:explode('/',$pattern);
          $resolved_ptr_exp = ($resolved_ptr =='/')?['/']:explode('/',$resolved_ptr);
           
          
        return ( count($pattern_exp) == count($resolved_ptr_exp) )?$this->matchRegxRouteAfterResolve($pattern,$resolved_ptr):false;
      }
       if(isset($this->regx[$pattern]))
       {  
         if(preg_match_all('#'.$this->regx[$pattern].'#',$resolved_ptr,$match) )
         {    
            
               return true;
         }
             return false;
       }

          return null;
   }

   public function all($method=null)
   {

      if($method)
      {
        $final=['pattern'=>[],'names'=>array_keys($this->names),'middlewares'=>$this->middlewares];

         foreach($this->routes as $key=>$route)
         {
             if($route['method'] == $method)
             {      
                  array_push($final['pattern'],$route['pattern']);
             }   
             else
             {
                continue;
             }
         }

         return $final;

      }
     return $this->routes;
   }

    private function matchRouteHolder($pattern,$path,$handler,$method)
    { 
         
        
       $pattern =rtrim($pattern,'/'); //registerd route pattern
       $path    =rtrim($path,'/');   // the request url to match against
          
         
         
        if( $pattern == $path )
        { 

              // dd($match);
            return ['path'=>$path,'handler'=>$handler,'pattern'=>$pattern];
        }
        elseif(preg_match_all('#\:([\w]+\??)#',$pattern,$match))
        {
            //unset($match[0]);
          
            $pattern_comp = preg_replace('#\:([\w]+)#','([\w]+)',$pattern); 

            $explode_path   = array_values(array_filter(explode('/',$path)));
            $explode_pattern = array_values(array_filter(explode('/',$pattern)));

            
            $differnce_of_both    = array_diff($explode_path,$explode_pattern);
           
             
        
            $filter_the_holder    = array_values(array_filter($differnce_of_both,function($v,$k){
                      return !strstr($v,':');
          },ARRAY_FILTER_USE_BOTH));
           
          $new_ur = str_replace(array_values($match[0]),$filter_the_holder,$pattern);  

          $params = preg_replace('/\:/','',$match[1]);

          $check_regx =$this->matchRegxRouteAfterResolve($pattern,$new_ur);
          
         // $params = array_combine($params,$differnce_of_both);//$this->combineParamsToValues($params,$differnce_of_both);

          if( ( $check_regx  === true && preg_match('#'.$pattern_comp.'#',$path) ) && count($explode_path) == count($explode_pattern))
          {

             return [ 
                     'path'=>$path,
                     'handler'=>$handler,
                     'params'=>$params,
                     'values'=>array_values($differnce_of_both),
                     'pattern'=>$pattern
                   ];
          }

           if(! preg_match('#'.$pattern_comp.'#',$path))
           {   
                 return false;
           }

           if(count($explode_path) !== count($explode_pattern))
           {
              return false;
           }
       
            //final match after url placeholder replacments
             
         
          if($new_ur !== $path)
          {
              return false;
          }

           // again check regx for high priority if fails route fails.
          // if regx === null regx not conduct to route and route matching will continue

          if($check_regx === false)
          {
             return false;
          }
        
         

            return [ 
                     'path'=>$path,
                     'handler'=>$handler,
                     'params'=>$params,
                     'values'=>array_values($differnce_of_both),
                     'pattern'=>$pattern
                   ];
        }

        else
        {
        // dd($pattern); 
           return $this->matchRegxRouteAfterResolve($pattern,$path,true) === true ?['path'=>$path,'handler'=>$handler,'pattern'=>$pattern]:false;
        }

    }

    protected function matchMethods($r_method,$req_method,$try=null)
    {   

        if($r_method == $req_method)
        {
            return true;
        }
        elseif($r_method =='head' && $req_method == 'get')
        {
             return true;
        }
        elseif($r_method =='put' && $req_method == 'post')
        {

             return $this->checkUnderScoreInputs($r_method);
        }
        elseif($r_method =='delete' && $req_method == 'post')
        {
            return $this->checkUnderScoreInputs($r_method);
        }
        elseif($r_method =='patch' && $req_method == 'post')
        {
             return $this->checkUnderScoreInputs($r_method);
        }
        elseif($r_method =='options' && $req_method == 'post')
        {
           return $this->checkUnderScoreInputs($r_method);
        }
        else
        {
            return false;
        }
       
    }

    private function resolve($method,$path)
    {
       
       if(!in_array($method,$this->valid_methods))
       {   
             $this->response->sendHeader('Allow:'.implode(',',$this->valid_methods));

             $this->response->httpResponse(405);

            throw new BadMethodCallException("Method not Allowed expecting :".implode(',',$this->valid_methods));
       }

       foreach(array_reverse($this->routes) as $route)
       {   
            if($this->matchRouteHolder($route['pattern'],$path,$route['handler'],$route['method']))
            {       

                 if($this->matchMethods($route['method'],$method))
                 {
                    return  $this->matchRouteHolder($route['pattern'],$path,$route['handler'],$route['method']);
                 }
                 elseif($this->matchRouteCount($route['pattern'],$this->routes))
                 {

                          continue;
                 }
                 else
                 {
                     return ['bad'=>$route['method']];
                 }
            }
    
       }

   }

   private function checkUnderScoreInputs($method)
   {  

       if(!$this->request->has('_'.$method) && $this->request->getMethod() =='post')
       {

                return false;
       }
       return true;
   }

   private function matchRouteCount($pattern,$routes)
   { 


      $count_route=[];

      foreach($routes as $route)
      {   
           if($route['pattern'] ==$pattern)
           {  
               $count_route[]=$route['method'];
           }
           else
           {
              continue;
           }
                   

      }

      $head_method_index = array_search('head',$count_route);

      if(in_array('head',$count_route))
      {
          unset($count_route[$head_method_index]);
      } 

     if(count($count_route)>1)
     {
         return true;
     }
     else
     {
        return false;
     }
     

   }

   public static function __callStatic(string $method,array $params)
   {
     

      if (!in_array($method,(new static)->valid_methods))
      {
        if(method_exists(self,$method))
        { 
            return (new static)->$method(...$params);
        }
        else
        {
             throw new Exception('The ' . $method . ' method is not supported.');
        }
      }

        return (new static)->$method(...$params);
    
   }



}