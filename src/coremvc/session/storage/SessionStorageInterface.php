<?php

namespace Core\Session\Storage;

use Core\Session\SessionInterface;

interface SessionStorageInterface{


  public function setSessionName($name);

  public function getSessionName();

  public function setSessionId($id);

  public function getSessionId();

  public function setSession($key,$value);

  public function setSessionArray($key,$value);

  public function getSession($key,$default=null);

  public function deleteSession($key);

  public function invalidateSession();

  public function flushSession($key);

  public function hasSession($key);



	
}