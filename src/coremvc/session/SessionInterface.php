<?php

namespace Core\Session;



interface SessionInterface{


  public function set($key,$value);

  public function setArray($key,$value);

  public function get($key,$default=null);

  public function delete($key);

  public function invalidate();

  public function flush($key);

  public function has($key);

	
}