<?php

namespace Core\Route;
use Core\Container\Container;
use ReflectionMethod;
use ReflectionClass;
use ReflectionParameter;
use Exception;

class RouteMethodDependencyResolver{

    private $container;
     
     public function __construct(Container $container)
     {
          $this->container = new $container;
     }

    public function resloveControlerMethod($controller_instance,$parameters)
    {


    }
}
