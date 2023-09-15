<?php

namespace Core\Configs;

interface ConfigInterface
{
	public  function load($file);
    public  function exists($key);
	public  function set($key,$value);
	public  function get($key,$default=null);
}