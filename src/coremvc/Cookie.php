<?php

/**
 * Core MVC Pattern
 * This a basic cookie context.
 * You can enhance class to accpet more options and enter more cookie details.
*/
namespace Core;


class Cookie
{ 

  
  public static function set($key,$value,$secure=false)
  {   
  	  $expired=time()+(1*365*24*60*60);
       
  	   return setcookie($key,$value,$expired,'/','',$secure,true);

  }


  public static function get($key,$default=null)
  {
       return (self::has($key))?$_COOKIE[$key]:$default;
  }

  public static function delete($key)
  {
      unset($_COOKIE[$key]);
      setcookie($key,null,-1,'/');
  }

  public static function invalidate()
  {    
       $_COOKIE=[];
  }


  public static function has($key)
  {
  	return isset($_COOKIE[$key]);
  }
	
}