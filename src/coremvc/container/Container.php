<?php

declare(strict_types=1);

namespace Core\Container;

use Core\Container\ContainerInterface;
use Core\Container\Exceptions\NotFoundException;
use Core\Container\Exceptions\DependcyIsNotIstantiableException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Exception;
use ReflectionUnionType;
use ReflectionNamedType;

class Container implements ContainerInterface{
    
    private $instance  = [];
    private $bind_args = [];

    public function set($id,string $concret=null)
    { 
      	$this->instance[$id]=$concret;

        return $this;
    }

    public function singleton(string $id, object $concret)
    {
        $this->instance[$id] = $concret;

        return $this;
    }

	public function get($id)
	{

      if($this->has($id))
      {
      	  $concret = $this->instance[$id];

      	  
         if($concret instanceof \Closure)
         {
                return $concret($this);

         }   
         if (is_object($concret))
         {
             return new $concret;
         } 	
             $id=$concret;  

      }

           return $this->resolve($id,$this->bind_args);
        
	}

	public function has($id)
	{
         return isset($this->instance[$id]);
	}

    public function bingArgs(array $params)
    {
         $this->bind_args =$params;

         return $this;
    }

    public function restArgs()
    {
         $this->bing_args=null;

         return $this;
    }

	public function resolve($concret,array $args=[])
	{

       
       $reflection  = new ReflectionClass($concret);

       if(!$reflection->isInstantiable())
       {
       	  throw new DependcyIsNotIstantiableException("class {$concret} is not istantiable");
       }
       $constructor = $reflection->getConstructor();

       if(is_null($constructor))
       {
       	  return new $concret;
       }

       $parameters = $constructor->getParameters();

       if(!$parameters)
       {
       	 return new $concret;
       }

       

       $dependences      = array_map(function(ReflectionParameter $pararm)use($concret){
                 
                 $name          = $pararm->getName();
                 $type          = $pararm->getType();
                

                 

                 if(!$type || $type instanceof ReflectionUnionType || $type->isBuiltin())
                 {
                     if(isset($this->bind_args[$name]))
                     {
                       
                        return $this->bind_args[$name];
                     }
                     elseif($pararm->isDefaultValueAvailable())
                     {
                         return $pararm->getDefaultValue();
                     }
                     elseif($pararm->isPromoted())
                     {
                          return $pararm;
                     }
                     else
                     {
                        throw new Exception("Faild to reslove parameter {$name} of {$concret} missing unknown params type"); 
                     }        	 

                 }

                // dd($type);
                 if($type instanceof ReflectionNamedType && ! $type->isBuiltin())
                 {
                 	return $this->get($type->getName());
                 }
                 	 throw new Exception("Faild to reslove parameter of {$concret} for invalid param {$name}");

       },$parameters);

        return $reflection->newInstanceArgs($dependences);
	}


    public function resloveClassMethod($instance,$method,array $args=[])
    {
        $name_method= $method;

        $method = new ReflectionMethod(
            $instance,
            $method
        );

        $parameters =    $method->getParameters();

        $num_of_method_params = count($parameters);
         
        $iterate=0;
        
        $arguments      = array_map(function(ReflectionParameter $pararm)use($args,$num_of_method_params,$instance,&$iterate,$name_method){
                 
                 $name = $pararm->getName();
                 $type = $pararm->getType();
                 
                 if(!$type || $type instanceof ReflectionUnionType || $type->isBuiltin())
                 {  
                    $index=$iterate;

                     if(count($args) == $num_of_method_params)
                     {   
                        
                        $iterate++;               
                        return $args[$index];
                     }
                     elseif($num_of_method_params >count($args) && count($args)!=0)
                     {  
                        $iterate++;
                         return $args[$index];
                     }
                     elseif($pararm->isDefaultValueAvailable())
                     { 
                         return $pararm->getDefaultValue();
                     }
                     else
                     {
                        $class_name =get_class($instance);

                        throw new Exception("Faild to reslove parameter of class {$class_name} method {$name_method} of  missing default value"); 
                     }           

                 }
                // dd($type);
                 if($type instanceof ReflectionNamedType && ! $type->isBuiltin())
                 {
                    return $this->get($type->getName());
                 }
       },$parameters);

      return $method->invokeArgs(
            $instance,
            $arguments
        );

    }

 
 /*
   //to resolve only parameters that not has an interface
	private function getDependences($params)
	{    
		//$reflection = new ReflectionParameter(...$params);
		 $dependences = [];
        foreach ($params as $parameter)
        {

             $dependenceClass = (string) $parameter->getType();
             $dependences[] = new $dependenceClass;
        }
        //dd($dependences);	
        return $dependences;
	}
  */

}