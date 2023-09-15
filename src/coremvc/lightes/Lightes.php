<?php

namespace Core\Lightes;
use Core\Lightes\LightesInterface;


class Lightes implements LightesInterface{
     
    private  $service_rooms;

	public function __construct(array $rooms)
	{
        $this->setRoom($rooms);
	}

	public function setRoom($rooms)
	{

           $this->service_rooms=$rooms;
	}

	public function on()
	{ 
		
		foreach($this->service_rooms as $service)
		{
			   $service =  app()::$container->get($service);
              
			   app()::$container->resloveClassMethod($service,'register');
			   app()::$container->resloveClassMethod($service,'startup');
			  
		}

	}

	


	public function off()
	{
        
	}


}