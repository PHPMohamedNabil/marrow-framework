<?php
/**
*=========Main project function used in this script============
*@author mohamed nabil
*@email  mohamedn085 at gmail dot com
*date  : 28-2-2018 11:58 PM
*/

use App\Core\View\Style;
use App\Core\App;
use App\Core\Container\Container;
use App\Core\Route\Router;
use App\Core\Session\Storage\SessionStorage;
use App\Core\Session\SessionFactory;



    function is_image($image)
    {  
        $case=false;

       if (is_file($image))
       {
    	   if (function_exists('finfo_open') === true)
           {
               $finfo = finfo_open(FILEINFO_MIME_TYPE);

               if (is_resource($finfo) === true)
               {
                      $result = finfo_file($finfo, $image);

                      if (strstr($result,'image/'))
                      {
                      	 $case=true;
                      }
               }

                  finfo_close($finfo);
           }
       }       
            
          return $case;
       

    }


    



   
    function getimageinfo($image,$remote=false)
    {    
         
         if ($remote)
         {
         	 $image=preg_replace('~(\s)~','%20',$image);

 
         	 return getimagesize($image);

         }
         return getimagesize($image);

    }

    

    function jpg_signature()
    {
        
        return ["\xFF\xD8\xFF\xE0","\xFF\xD8\xFF\xE8","\xFF\xD8\xFF\xE3","\xFF\xD8\xFF\xE2","\xFF\xD8\xFF\xE1"];

    }

    function gif_signature()
    {
       return ["\x47\x49\x46\x38\x37\x61","\x47\x49\x46\x38\x39\x61"];

    }

    
    function png_signature()
    {
        
        return ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"];

    }

 
    function is_jpeg($image)
    {   
    	$case=false;
    	if(is_image($image))
    	{
          if(!$fp = fopen ($image, 'rb')) return 0;
              /* Read 20 bytes from the top of the file */
           if(!$data = fread ($fp, 20)) return 0;

          if(bin2hex($data[0]) === 'ff' && bin2hex($data[1]) === 'd8')$case=true; fclose($fp);return $case;
    	}

    	return $case;
 
    }


    function is_png($image)
    {   
    	$case=false;
        if(is_image($image))
    	{
          if(!$fp = fopen ($image, 'rb')) return 0;
            /* Read 20 bytes from the top of the file */
          if(!$data = fread ($fp, 20)) return 0;
      
            if (bin2hex($data[0]) === '89' && bin2hex($data[1]) === '50' && bin2hex($data[2]) === '4e' && bin2hex($data[3]) === '47' && bin2hex($data[4]) === '0d' && bin2hex($data[5]) === '0a' && bin2hex($data[6]) === '1a' && bin2hex($data[7]) === '0a')$case=true; fclose($fp);return $case;
        }

        return $case;
    }

    function is_gif($image_file)
    {
       if(is_image($image_file))
       {
      /* Open the image file in binary mode */
      if(!$fp = fopen ($image_file, 'rb')) return 0;
 
        /* Read 20 bytes from the top of the file */
      if(!$data = fread ($fp, 20)) return 0;
 
    /* Create a format specifier */
      $header_format = 'A6version';  # Get the first 6 bytes

    /* Unpack the header data */
      $header = unpack ($header_format, $data);
 
      $ver = $header['version'];
        
        return ($ver == 'GIF87a' || $ver == 'GIF89a')? true : false;
      }
 
    }
            

    function image_mime_type($image)
    {
      return image_type_to_mime_type(exif_imagetype($image));
    }


    function jpeg_camera_info($imagePath)
    {

    // Check if the variable is set and if the file itself exists before continuing
    if (isset($imagePath) && is_file($imagePath) && is_jpeg($imagePath)) {
    
      // There are 2 arrays which contains the ingres_fetch_object(result)rmation we are after, so it's easier to state them both
      $exif_ifd0 = read_exif_data($imagePath ,'IFD0' ,0);       
      $exif_exif = read_exif_data($imagePath ,'EXIF' ,0);
      
      //error control
      $notFound = "Unavailable";
      
      // Make 
      if (@array_key_exists('Make', $exif_ifd0)) {
        $camMake = $exif_ifd0['Make'];
      } else { $camMake = $notFound; }
      
      // Model
      if (@array_key_exists('Model', $exif_ifd0)) {
        $camModel = $exif_ifd0['Model'];
      } else { $camModel = $notFound; }
      
      // Exposure
      if (@array_key_exists('ExposureTime', $exif_ifd0)) {
        $camExposure = $exif_ifd0['ExposureTime'];
      } else { $camExposure = $notFound; }

      // Aperture
      if (@array_key_exists('ApertureFNumber', $exif_ifd0['COMPUTED'])) {
        $camAperture = $exif_ifd0['COMPUTED']['ApertureFNumber'];
      } else { $camAperture = $notFound; }
      
      // Date
      if (@array_key_exists('DateTime', $exif_ifd0)) {
        $camDate = $exif_ifd0['DateTime'];
      } else { $camDate = $notFound; }
      
      // ISO
      if (@array_key_exists('ISOSpeedRatings',$exif_exif)) {
        $camIso = $exif_exif['ISOSpeedRatings'];
      } else { $camIso = $notFound; }
      
      $return = array();
      $return['make'] = $camMake;
      $return['model'] = $camModel;
      $return['exposure'] = $camExposure;
      $return['aperture'] = $camAperture;
      $return['date'] = $camDate;
      $return['iso'] = $camIso;
      return $return;
    
      }     else {
           return false; 
       } 
   
    }



    function imageinfoarray($imgarr)
    {
	    $links = (is_array($imgarr))?$imgarr:false;
        $sizearray = array();
        $count = count($links);

       for($i = 0; $i < $count; $i++)
       {
           $size = getimagesize($links[$i]);
           list($width, $height) = $size;
           $sizearray[$links[$i]] = array("width" => $width, "height" => $height);
        
        }

        return $sizearray;
    }


    function filter_image($image,$filtertype)
    {
           

    }


    function validate_int($var)
    {   if (is_integer($var))
    	{
    	   return $var;
    	}
    	return null;

    }

    function csrf_ajax($request)
    {
          if(isset($request->headers['X-CSRF-TOKEN']))
          {
            if(session()->get('csrf_time') && !is_csrf_token_expired())
            {
                if (!hash_check(session_token(),$request->headers['X-CSRF-TOKEN']))
                {
                
                   return false;
            
                }
              
            
                  return session_token();
            }
            else
            {    session()->delete('__token');
                 session()->delete('csrf_time');
                 session_token();
                 return false;
            }

          }

          return false;
    }

    function csrf_token()
    {  
       
       $token=session_token();
      
       if (isset($_POST) && post('_tcsrf'))
       {
       	   
            if(session()->get('csrf_time') && !is_csrf_token_expired())
            {
                if (!hash_check($token,getpostvalue('_tcsrf')))
                {
                
                   return false;
            
                }
                 
            
                return session_token();
            }
            else
            {   
                session()->delete('__token');
                session()->delete('csrf_time');
                 session_token();
                 return false;
            }

       }

       return null; 
    }

    function is_csrf_token_expired()
    {
        $life_time =session()->get('csrf_time','');
            
         if( (time()-$life_time) >= 900 )
         {

            return true;   
         }
         
         return false;      
        
    }

 
    function session_token()
    {
      if ( ( !session()->has('__token') && !session()->has('csrf_time') ) || (is_csrf_token_expired() ) )
      {
         
        session()->set('csrf_time',time()+900);
        session()->set('__token',hash_generate(generate_random_string(130)));
          
          return   session()->get('__token');
      } 
        
        return session()->get('__token');

    }


    
    function generate_random_string($stringlength)
    {
        $string=str_shuffle('ABCDEFGHIJKLOMNQRPTSWXYZabcdefghijklmnopqrstvwxyz0123456789f/21.234&^%$@#@!#$%^&*^1/2mv2!@#!@@!#$@%#^$&%UYHRGDFVCXZ!@#$%^^&*^(&)*_+()(*&^%^$#@@!WQ	SAz|ABCDEFGHIJKLOMNQRPTSWXYZabcdefghijklmnopqrstvwxyz0123456789f/21.234&ABCDEFGHIJKLOMNQRPTSWXYZabcdefghijklmnopqrstvwxyz0123456789f/fQRPTSWXYZabcdefghijklmnopqrstvwxyz0123456789f/21.234&');

        $splite=str_split($string);
        
        $result='';

        for ($i=0; $i <$stringlength; $i++) { 
        	 
        	 $result.= "".$splite[array_rand($splite)];
        }

        return $result;
    }

    function hash_generate($data)
    {
         
         return hash('sha256',$data); 

    }
    
    function hash_check($hash,$data)
    {
       
       return hash_equals($hash,$data);

    }


   

    function post($name)
    {  
      if ($_SERVER['REQUEST_METHOD'] === 'POST')
      {
           return filter_input(INPUT_POST,$name);
      }

      return false;
      
    }

    function get_request_method()
    {
       return strtolower($_SERVER['REQUEST_METHOD']??'');

    }
    
   function redirect_to($location,$code=302)
    {
        if(!headers_sent()) {
            //If headers not sent yet... then do php redirect
            header("Location: $location",true,$code);
            exit; // to prevent any opening
        } else {
            //If headers are sent... do javascript redirect... if javascript disabled, do html redirect.
            $red    =  '<script type="text/javascript">';
            $red   .=  'window.location.href="'.$location.'";';
            $red   .=  '</script>';
            echo $red;

            /*---------- HTML Meta Refresh ---------*/
            $meta  =  '<noscript>';
            $meta .= '<meta http-equiv="refresh" content="0;url='.$location.'" />';
            $meta .= '</noscript>';
            echo $meta;
            exit; // to prevent any opening
        }

    }
    
  

    function getpostvalue($name)
    {
       return post($name);
    }
    


     function getgetvalue($name)
    {
       return get($name);
    }
    

    function get($name)
    {  
      if (get_request_method() === 'GET')
      {   
        
        return filter_input(INPUT_GET,$name);
           
      }
      return false;
      
    }

    function view($name,$data=[],$string=false)
    { 
        return app()->style->render($name,$data,$string);
    }


    function dump_native($data)
    {
         ob_start();
          var_dump($data);
         $result = ob_get_clean();
         $result = str_replace('=>','-->',$result);
         $result = preg_replace('#(\w*\(.*\))#'," <label class=\"tab-label\" for=\"chck1\">$1</label>",$result);
        // $result = preg_replace('#()(.*)#'," <label class="tab-label" for="chck1">$1</label>",$result);
         $result='<div style="background-color:#eaeaea;color:#2d2d2d;width:600px;height:600px;overflow:auto;font-weight:bold;">'.$result.'</div>';
         //require_once CORE.'dd_style.php';
        return die('<pre>'.$result.'</pre>');
    }



    function dump_view($data)
    {
         ob_start();
          var_dump($data);
         $result = ob_get_clean();
         $result = str_replace('=>','-->',$result);
         $result1 = preg_replace('#(\w*\(.*\))#',"<label class=\"tab-label\" for=\"chck1\">$1</label>",$result);
         $final = preg_replace('#(</label>(.*)<label>)#',"<div class=\"tab-content\">$1</div>",$result1);

        return die(require_once(CORE.'dd_style.php'));
    }

    function abort($status)
    {
        switch ($status) {
            case 404:
                http_response_code(404);
                die(view('errors.error',['message'=>'Resource Data not found','respond'=>404]));
            break;
            default:
             die('somthing went wrong cannot process your Request');
                break;
        }
    }

    function bread_crump($base_link,$links)
    {
        if(is_array($base_link) && is_array($links) )
        {
            
          $bread_crump='<ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="'.$base_link['link'].'">'.$base_link['name'].'</a></li>';

            foreach ($links as $name => $link)
            {
               if(strstr($name,'.end'))
               {
                 $bread_crump.='<li class="breadcrumb-item active" aria-current="page">'.strstr($name,'.end',true).'</li>';
               }
               else{
                 $bread_crump.='<li class="breadcrumb-item"><a href="'.$link.'">'.$name.'</a></li>';
               }

                
            }

            $bread_crump.='</ol>';

            return $bread_crump;

        }

        return null;
        
    }
    function array_match($arr1,$arr2,$highlevel=false)
   {

       $keys1=array_keys($arr1);
       $keys2=array_keys($arr2);

       $values1=array_values($arr1);
       $values2=array_values($arr2);
   
     if ($highlevel)
     {
    
         if ($keys1 === $keys2 && $values1 === $values2)
         {
        
            return true;

         }

       return false;
     }

     if ($keys1 == $keys2 && $values1 == $values2)
     {
        
         return true;

     }
       return false;
   
   }


    function old($data)
    {
       return get_request_method()=='GET'?get($data):post($data);
    }

    
if (!function_exists('getallheaders')) 

{

    function getallheaders() 

    {

           $headers = [];

       foreach ($_SERVER as $name => $value) 

       {

           if (substr($name, 0, 5) == 'HTTP_') 

           {

               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;

           }

       }

       return $headers;

    }
}

 function emu_getallheaders() {

        foreach ($_SERVER as $name => $value) 

       {

           if (substr($name, 0, 5) == 'HTTP_') 

           {

               $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));

               $headers[$name] = $value;

           } else if ($name == "CONTENT_TYPE") {

               $headers["Content-Type"] = $value;

           } else if ($name == "CONTENT_LENGTH") {

               $headers["Content-Length"] = $value;

           } 

       }

       return $headers;

    }

    function get_known_broswer($random_gus=false,$agent_only=false)
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $browser = "N/A";

        if($agent_only)
        {
             return $user_agent;
        }
        
        if($random_gus)
        {
            $user_agent = explode(' ',$user_agent);

            return isset($user_agent[10])?$user_agent[10]:get_known_broswer();
        }


        $browsers = [
            '/(msie\/[0-9.]+[^\w+])/i' => 'Internet explorer',
            '/(firfox\/[0-9.]+[^\w+])/i' => 'Firefox',
            '/(safari\/[0-9.]+[^\w+])/i' => 'Safari',
            '/(chrome\/[0-9.]+[^\w+])/i' => 'Chrome',
            '/(edge\/[0-9.]+[^\w+])/i' => 'Edge',
            '/(opera\/[0-9.]+[^\w+])/i' => 'Opera',
            '/(mobile\/[0-9.]+[^\w+])/i' => 'Mobile browser',
        ];

        foreach ($browsers as $regex => $value) {
            if (preg_match($regex, $user_agent,$match)) {
                $browser = $value.':'.$match[1];
            }
        }

        return $browser;
    }

    function public_path($file)
    {
        return rtrim(PUBLIC_PATH,DS).DS.$file;
    }

    function asset($path)
    {
       return rtrim(SITE_URL,'/').'/'.ltrim('assets/'.$path);
    }

    function css($path)
    {
        return rtrim(SITE_URL,'/').'/'.ltrim('css/'.$path);
    }

    function js($path)
    {
        return rtrim(SITE_URL,'/').'/'.ltrim('js/'.$path);
    }

    function upload($path)
    {
        return rtrim(SITE_URL,'/').'/'.ltrim('uploads/'.$path);
    }

    function url($url='')
    {
        return SITE_URL.$url;
    }

    function special_chars($val)
    {
         return htmlspecialchars($val,ENT_QUOTES | ENT_HTML5,'UTF-8');
    }


    function route_name($name,$params=[])
    {   
        $route=app()->route->getRouteName($name,$params)??'/';

        return ($name)?rtrim(SITE_URL,'/').'/'.ltrim($route,'/'):'/';
    }

    function url_file($path_file)
    {
         return rtrim(SITE_URL,'/').'/'.$path_file;
    }

    function path_to_public($file='')
    {
        return PUBLIC_PATH.$file;
    }

    function app()
    {  
        return App::$app;
    }

    function session()
    { 
         return app()->session;
    }

    function text_rev($text)
    {
        return implode('',array_reverse(preg_split('//',$text,-1,PREG_SPLIT_NO_EMPTY)));
    }

    function config()
    {
        return app()::$config;
    }

    if(!function_exists('env'))
    {

    function env($name,$default=null)
    {
       
        return isset($_ENV[$name])?$_ENV[$name]:$default;
    }

    }

    function csrf_input()
    {
      return '<input type="hidden" name="_tcsrf" value="'.session_token().'" />';
    }


    function input_method($method)
    {
         return '<input type="hidden" name="_'.$method.'" />';
    }

    if(!function_exists('old_body'))
    {
         function old_body($param)
         {
             return session()->has('old')?session()->flush('old')[$param]:null;
         }
    }


    function error_handler($errno, $errstr, $errfile, $errline) {
      if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        return false;
      }

      // Supported error types
      $php_error_types = [
        E_WARNING => 'E_WARNING',
        E_NOTICE => 'E_NOTICE',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        E_ALL => 'E_ALL'
      ];

      $error_content = '<p><b>' . $php_error_types["$errno"] . '</b></p>' . $errstr . '</pre>';
      $error_content .= '<p>The error occurred on line <b>' . $errline . '</b> in file: </p><pre>' . $errfile . '</pre>';
      $error_content .= '<p><i>PHP ' . PHP_VERSION . ' (' . PHP_OS . ')</i></p>';
      $error_content .= '<p>This should not happen. Ideally all <i>notices</i>, <i>warnings</i>, and <i>errors</i> should be handled in your code. To avoid this:</p>';
      $error_content .= '<ol>
       <li>Always define variables before you use them.</li>
       <li>Remember to check that a file exists before including it.</li>
       <li>Always handle potential errors according to coding standards. I.e. Show a relevant error to the user, fail silently, or log events to a file on the server.</li>
       </ol>';

      $error_content .= '<p class="indent"><b>Note.</b> The above is probably not a complete list.</p>';

      echo $error_content;

      /* Do not execute PHP internal error handler */
      return true;
    }
        //print_r($sizearray);
    // which will print out: Array ( [test1.jpg] => Array ( [width] => 300 [height] => 400 ) [test2.png] => Array ( [width] => 680 [height] => 100 ) )