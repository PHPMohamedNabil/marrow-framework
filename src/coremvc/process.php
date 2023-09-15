<?php 


     function has_file($file)
     {
            return $_FILES[$file]['tmp_name'] !=='';
     }

     function get_file($file)
     {  
     	if (isset($_FILES))
     	{
     	   
     	   return $_FILES["$file"]['tmp_name'];
     	}

     	return false;
     }


    function set_password($password)
    {
       return password_hash($password,PASSWORD_BCRYPT,['cost'=>11]);
    }

     function passwordCheck($password,$hashedpassword)
    {
        
      return password_verify($password,$hashedpassword);
         


    }


    function getfiledata($file)
    {    
        if (file_exists($file))
        {
             $data=file_get_contents($file);

              return $data;
        }

    }

    function filewrite($file,$data,$append=false)
    {  
        if (file_exists($file) && !$append)
        {     
           
            file_put_contents($file,$data);
        }
        else{
          file_put_contents($file,$data,FILE_APPEND);
        }
        

    }