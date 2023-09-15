<?php

namespace Core;

use GuzzleHttp\Psr7\ServerRequest;
use HttpSoft\ServerRequest\UploadedFileCreator;

class Upload{

     protected $max_file_size;

     protected $uplad_file;

     protected $supported_exten;

     public    $errors;

     protected $fileExten;


     public function __construct()
     {

     }

     public static function info()
     {
       $request = ServerRequest::fromGlobals();
        $files = $request->getUploadedFiles();
        $uploadedFiles = $request->getUploadedFiles();
     
        return $uploadedFiles;
     }

}