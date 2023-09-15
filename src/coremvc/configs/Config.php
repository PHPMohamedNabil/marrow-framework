<?php

namespace Core\Configs;

use  Core\Configs\ConfigInterface;

class Config implements ConfigInterface{

	protected $data;
	protected $default=null;
	private  static $get_instance;
    
     public static function getInstance()
    {
        if(!self::$get_instance)
        {
            self::$get_instance=new static;
        }
        return self::$get_instance;
    }

	public  function load($file)
	{
		$this->data = require $file;
	}

	public function get($key,$default=null)
	{
		$this->default = $default;

		$segments = explode('.',$key);
		$data = $this->data;
         

		foreach($segments as $segment)
		{
			  if(isset($data[$segment]))
			  {
			  	 $data = $data[$segment];
			  }
			  else
			  {
			  	 $data =$this->default;
			  	 break;
			  }
		}
          
		return $data;

	}

	public  function exists($key)
	{
		 return $this->get($key) !== $this->default;
	}

	public function set($keys, $value = null)
    {
        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $this->set($key, $value);
            }

            return $this;
        }

        $items = &$this->data;

        if (is_string($keys)) {
            foreach (explode('.', $keys) as $key) {
                if (!isset($items[$key]) || !is_array($items[$key])) {
                    $items[$key] = [];
                }

                $items = &$items[$key];
            }
        }

        $items = $value;
           
        
        return $this->data;
    }

}