<?php

namespace Core\Session;

use Core\Session\SessionInterface;
use Core\Session\Exceptions\SessionException;
use Core\Session\Exceptions\SessionInvalidArgumentException;
use Core\Session\Storage\SessionStorageInterface;

class Session implements SessionInterface
{

     protected SessionStorageInterface $storage;

     protected $session_name;

     protected const SESSION_PATTERN='/^\w+$/';

     private $agent_patt   = 'CORE_BROWSER';


     public function __construct($session_name,SessionStorageInterface $storage=null)
     {
     	   if($this->isSessionKeyValid($session_name) === false)
     	   {
                throw new SessionInvalidArgumentException("Session {$session_name} is not a valid session name.");
                   
     	   }

     	   $this->storage      = $storage;

             $this->stamp();
             $this->setStartTime();
             $this->last_access();
             $this->checkLifeTime();
     
     }

     public function set($key,$value)
     {
     	  try
     	  {
     	  	$this->storage->setSession($key,$value);
     	  }
     	  catch(Throwable $th)
     	  {
     	  	throw new SessionException('setting $key session error');
     	  }
     }

     public function setArray($key,$value)
     {  
     	  try
     	  {
     	  	$this->storage->setArraySession($key,$value);
     	  }
     	  catch(Throwable $th)
     	  {
     	  	throw new SessionException('setting $key session array error');
     	  }


     }

     public function get($key,$default=null)
     {
     	if(!$this->has($key))
     	{
             return $default;
     	}
     	return $this->storage->getSession($key,$default);

     }

     public function delete($key)
     {
     	 return $this->storage->deleteSession($key);
     }

     public function invalidate()
     {

     	 return $this->storage->invalidateSession();
     }

     public function flush($key)
     {
     	return $this->storage->flushSession($key);
     }

     public function setStartTime()
     {
            if(!$this->has('start_time'))
            {

                 $this->set('start_time',time()); 
                
            }

            return null;
     }

       public function checkLifeTime()
       {   
       
           $life_time =$this->get('start_time');
            
             if( (time()-$life_time) >= 60 * intval($this->storage->getLifeTime()) )
             {
                return $this->invalidate();     
             }

             return true;
       
        }

        public function last_access()
        {  
          
          if( $this->has('last_access'))
          {   
              $last =$this->get('last_access');

              if(( (time()-$last) >= 60 * intval($this->storage->idleTime()) ) )
              {
                 return $this->storage->regenerate();
              }

              return true;
              
          }
          else
          {
             return $this->set('last_access',time()+$this->storage->idleTime());
          }
             
        }


       public function stamp()
       { 
          $agent   = get_known_broswer(false,true);
          $pattern = $agent.$this->agent_patt.$this->storage->getSessionId();
          
          if( !$this->has('stamp') )
          {
                    
               return $this->set('stamp',$pattern);
          }
          elseif($pattern == $this->get('stamp'))
          {
        
               return true;
          }
          else
          {
                    
              $this->invalidate();
          }
          
       }

     public function has($key)
     {
     	return $this->storage->hasSession($key);
     }

     public function isSessionKeyValid($key)
     {
     	return preg_match(self::SESSION_PATTERN,$key) ==1;
     }



	
}