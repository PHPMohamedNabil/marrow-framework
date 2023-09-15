<?php

namespace App\Core;

use Core\Request;
use Core\Response;
use AllowDynamicProperties;
#[AllowDynamicProperties]
class Url{

    private $request;

    private  static $redirect;

   private function __construct()
   {
        $this->request  = new Request;
       $this->response = new Response;
   }

   public static function path($path)
   {
   	  return (new self)->request->url().$path;
   }

   public static function redirect($path=null,$code=302)
   {
   	   if($path)
   	   {
   	   	 return redirect_to($path,$code);
   	   }

         self::$redirect = true;

   	   return new static;
   } 
   
   public static function redirectWith($path,array $data)
   {     
           if($data)
           {
              foreach($data as $key=>$value)
              {
                session()->set($key,$value);
              }
            
           }

   	     return  self::redirect($path);
   }

   public static function back()
   {
   	    return self::redirect((new self)->request->previous());
   }

   public static function backWith(array $data)
   {    
     	
         foreach($data as $key=>$value)
         {
                session()->set($key,$value);
         }
            
    

   	    return self::redirect((new self)->request->previous());
   }

   public static function route($name)
   {
       return route_name($name);
   }
   

   public static function fails()
   {    
   	   (new self)->response->httpResponse(404);
   	   return view(404);
   }



}