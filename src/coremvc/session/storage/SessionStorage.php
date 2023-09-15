<?php

namespace Core\Session\Storage;

use Core\Session\SessionInterface;
use Core\Session\Storage\SessionStorageInterface;
use SessionHandler;
use SessionIdInterface;
use SessionUpdateTimestampHandlerInterface;
class SessionStorage extends SessionHandler implements SessionStorageInterface,SessionIdInterface,SessionUpdateTimestampHandlerInterface
{

	protected array $options=[];

	//private $ciphering_value    = "AES-128-CTR";

	//private $encryption_key     = 'CORE';

	private $session_id_check;


  

	  public function __construct($options=[])
	  {

          $this->options=$options;
             
          $this->iniSet();
        
        
	  }

	  public function iniSet()
	  {
			ini_set('session.gc_maxlifetime',$this->options['gc_maxlifetime']);
			ini_set('session.gc_divisor',$this->options['gc_divisor']);
			ini_set('session.gc_probability',$this->options['gc_probability']);
			ini_set('session.use_cookies',1);
			ini_set('session.cache_limiter','nocache');
			ini_set('session.use_trans_sid',0);
			ini_set('session.use_strict_mode',1);
			ini_set('session.use_only_cookies',1);
			ini_set('session.save_handler','files');



	       $this->setSessionName($this->options['session_name']);	 
			 session_save_path($this->options['save_path']);  
          session_set_save_handler($this,true);
  
	  	     $domain = $this->options['domain'];
		     $secure = $this->options['secure'];
	  	
	  		session_set_cookie_params($this->options['lifetime'],$this->options['path'],$domain,$secure,$this->options['httponly']);
	     
	  
     
        
        if($this->is_session_started())
        {  
        	   session_unset();
        	   session_destroy();
        }
         
            
		    	$this->startSession();
	

     
	  }

	  public function is_session_started()
    {
	    if ( php_sapi_name() !== 'cli' )
	    {
	        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
	            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
	        } else {
	            return session_id() === '' ? FALSE : TRUE;
	        }
	    }
	    return FALSE;
   }  

	  public function startSession()
	  {
		    if(session_status() === PHP_SESSION_NONE)
		  	{
           session_start();
           
		  	}
 
	  }


	  public function getSessionName()
	  {
		  return $this->options['session_name'];
	  }

	  public function setSessionId($id)
	  {
	    	return session_id($id);
	  }

	  public function getSessionId()
	  {
	  	   return session_id();
	  }

	  
    public function setSessionName($name)
    {
      	 return  session_name($name);
    }


	   public function setSession($name,$value)
	   {
	        $_SESSION[$name]=$value;
	   }
 

	   public function setSessionArray($key,$value)
	   {    
	  	  $_SESSION[$key][]=$value;
	  	 
	   }

	   public function getSession($key,$default=null)
	   {
	  	 if($this->hasSession($key))
	  	 {
	  	 	 return $_SESSION[$key];
	  	 }
	  	 return $default;
	   } 

	  public function deleteSession($key)
	  {
	  	if($this->hasSession($key))
	  	{
	  	    unset($_SESSION[$key]);
	  	    return true;	
	  	}
	  	return false;
	  }

	  public function getLifeTime()
	  {
	  	 return $this->options['session_life_time'];
	  }

	  public function invalidateSession()
	  {
	  	 $_SESSION =[];
	  	 if(ini_get('session.use.cookies'))
	  	 {
	  	 	$params = session_get_cookie_params();
	  	 	setCookie($this->getSessionName(),'',time()-$params['lifetime'],$params['path'],$params['domain'],$params['secure'],$params['httponly']);
	  	 }
	  	 session_unset();
	  	 session_destroy();
	  }

	  public function flushSession($key)
	  {
         
	  	 if($this->hasSession($key))
	  	 {
	  	     $content = $this->getSession($key);
	  	     $this->deleteSession($key);
	  	     return $content;
	  	 }
	  	 return null;	 
	  }

	  public function idleTime()
	  {
	  	 return $this->options['session_last_access'];
	  }


    public function regenerate()
	  {
	  	return session_regenerate_id(true);
	  }


    public function hasSession($key)
    {
	  	 return isset($_SESSION[$key]);
    }

    public function open($path,$session_name)
    {  
    	clearstatcache();
	     
	     if(!file_exists($this->options['save_path']))
	    {
	    	  @mkdir($this->options['save_path'],0777);
	    }


      return  parent::open($this->options['save_path'],$this->getSessionName());    
    }
    

	   public function read($id)
     {   
     	
     	 	 return parent::read($id);
     
     }

     public function write($id, $data)
     {

        return parent::write($id, $data);
     }

     public function validateId($key)
     {  
     	 $file=rtrim($this->options['save_path'],DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'sess_'.$key;
     	 
     	 clearstatcache();
     	
     	 if(file_exists($file) && (time()- fileatime($file)) <$this->getLifeTime() * 60 )
     	 { 
            clearstatcache();
     	 	   return true;
     	 }  
       	
     	 return false;
     
     }

     public function close()
     {
     	  return parent::close();
     }

     public function destroy($key)
     {
     	 return parent::destroy($key);
     }

     public function create_sid()
     {
         return  parent::create_sid();
     }

    public function updateTimestamp($id, $data)
    {
    	return true;     
    }
        
    

}
