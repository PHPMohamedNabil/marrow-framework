<?php
//https://github.com/rakit/validation
namespace Core\Validation;

use Rakit\Validation\Validator;
use Core\Url;
use Core\Request;

class Validate{


    public static function validate(array $rules,$body,$json=null,$custom_rules=[])
    {
        $validator = new Validator;
        
      if(count($custom_rules)>1)
      {
         $validator->addValidator($custom_rules[0],$custom_rules[1]);
      }

        $validation = $validator->validate($_POST + $_FILES,$rules);
      
        $errors = $validation->errors();
     
     

      if ($validation->fails())
      {
        
          if ($json)
          {
                return ['errors' => $errors->firstOfAll()];
          }
          else
          {
               
                
               return Url::backWith([
                                     'errors'=>$errors,
                                     'old'=> $body->getBody()
                                     ]);
          }

      }

      return true;
          

   }

   public static function customRule()
   {

   }

   public static function langFile()
   {

   }


	
}
