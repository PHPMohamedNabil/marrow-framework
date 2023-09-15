<?php

namespace Core\Session;

use Core\Session\SessionInterface;
use Core\Session\Exceptions\SessionInvalidArgumentException;
use Core\Session\Storage\SessionStorageInterface;

class SessionFactory{


    public static function create($storage,$options)
    {  
    	$storage_object = new $storage($options);
            
    	if(!$storage_object instanceof SessionStorageInterface)
    	{
    	 	throw new SessionInvalidArgumentException('session storage class must be instanceof SessionStorageInterface');
    	}

    	return new Session($options['session_name'],$storage_object);

    }
	
}