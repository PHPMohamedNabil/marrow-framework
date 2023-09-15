<?php

namespace Core\Lightes;

use Core\Lightes\LightesInterface;

class LightesFaced{
    
    protected $lightes;
	
	public function __construct(LightesInterface $lightes)
	{
        $this->lightes = $lightes;
    }

    public function turnOn()
    {
        return $this->lightes->on();
    }

    public function turnOff()
    {
        return $this->lightes->off();
    }

}