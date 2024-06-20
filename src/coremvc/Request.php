<?php
// to make an outbound request use guzzle request PSR Standrds libraray
namespace Core;

use Core\Validation\Validate;
use Core\session\Session;
use GuzzleHttp\Psr7\UploadedFile;
use GuzzleHttp\Psr7\Stream;

class Request{

    private array $body = [];
    public $status;
    public array $headers=[];
    protected $method;
    protected $uri;
    public $file;

    public function __construct($uri='',$body='',$headers='',$method='')
    {  
    	if($uri && $body && $headers && $method)
    	{ 
    		$this->uri     = $uri;
    		$this->body    = $body;
    		$this->headers = $headers;
    		$this->method  = $method;

    	}
    	else
    	{
    		$this->method = ($method)?strtolower($method):get_request_method();
	    	$this->getMethod();
	    	$this->getBody();
	    	$this->getUri();
	    	$this->getGlobalHeaders();
    	}

        $this->file=Upload::info();
       
    }

	public function getMethod()
	{

      if ($this->method == 'post' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER))
      {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'delete';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'put';
            }
            else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PATCH') {
                $this->method = 'patch';
            }
            else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'HEAD') {
                $this->method = 'head';
            } 
             else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'OPTIONS') {
                $this->method = 'options';
            }else {
                throw new \IncorrectHeaderException("Unexpected Request Method at Header");
            }
      }
          return $this->method;
	}

	public function getPath()
	{   
	   $path = $this->uri;  
	   $pos  = strpos($path,'?');
		if($pos === false)
		{
            return $path;
		}
		$path = substr($path,0,$pos);
		return $path;
          
	}

    public function getUri()
	{   
		$this->uri=$_SERVER['REQUEST_URI']??'';
		return $this->uri;
	}

	public function url()
	{   
		$url =  $this->uri; 
        $url = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST); 

        return $url;
	}

	public function get($name)
	{
		return isset($this->body[$name])?$this->body[$name]:null;
	}

	public function qString()
	{
		 return $_SERVER['QUERY_STRING']??[];
	}

	public function getBody()
	{

        if($this->getMethod() == 'get')
		{
			foreach($_GET as $key=>$value)
			{
				$this->body[$key]=trim(filter_input(INPUT_GET,$key,FILTER_SANITIZE_FULL_SPECIAL_CHARS));

			}
			return $this->body+$_FILES;
		}

		if($this->getMethod() == 'post')
		{
			foreach($_POST as $key=>$value)
			{
				$this->body[$key]=trim(filter_input(INPUT_POST,$key,FILTER_SANITIZE_SPECIAL_CHARS));
			}
			return $this->body+$_FILES;
		}

	    if($this->body)
        {
		   return $this->body;
        }

		return $this->body+$_FILES;
	}

	public function isPost($key)
	{
         return post($key);
	}

	public function isGet($key)
	{
         return get($key);
	}

	public function isAjax()
	{
	   if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' )
	   {
                return true;
       
       }  
          return false;  
	}

	public function setHeader($header,$value)
	{
		  $this->headers[$header]=$value;
	}

	public function getHeaders()
	{  
		return $this->headers;
	}

	public function getGlobalHeaders()
	{
		$this->headers = getallheaders();
	}

	
    public function file($name)
    {
    	 return $this->file[$name];
    }
	

	public function hasFile($file)
	{   
		return has_file($file);
	}

	public function reqAll()
	{
		return $this->body;

	}

	public function has($key)
	{
		return isset($this->body[$key]);
	}
    
    public function previous()
	{
	  return isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:null;
	}

	public function validate($rules,$json=null,$custom_rules=[])
	{
		 return Validate::validate($rules,$this,$json,$custom_rules);
	}

	public function session()
	{
		return session();
	}
  

	public function __set($key,$value)
	{
       if(!isset($this->body[$key]))
       {
       	  $this->body[$key]=$value;
       }

	}


	public function __get($key)
	{
       return isset($this->body[$key])?$this->body[$key]:null;
	}

}
