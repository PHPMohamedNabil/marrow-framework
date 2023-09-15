<?php

namespace Core;

use OutOfRangeException;

class RegisteryPattern{

     
     protected static $classes = [];


     public static function get($class)
     {
         if(!isset(self::$classes[$class]))
         {
           throw new OutOfRangeException('class service is not registered in array of classes');
         }

          return self::$classes[$class];
     } 

     public static function set($name,$class)
     {
        self::$classes[$name]=$class;
     }

     public static function eject($name)
     {
           self::$classes[$name]=NULL;
          unset(self::$classes[$name]);
          return null;
     }

        //! Prohibit cloning
    private function __clone() {
    }

    //! Prohibit instantiation
    private function __construct() {
    }



}