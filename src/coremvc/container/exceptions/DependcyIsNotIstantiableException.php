<?php

declare(strict_types=1);

namespace Core\Container\Exceptions;

use Core\Container\ContainerInterface;
use Core\Container\ContainerExceptionInterface;

use Exception;

class DependcyIsNotIstantiableException extends Exception implements ContainerExceptionInterface{

 

}