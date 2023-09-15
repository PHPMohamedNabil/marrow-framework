<?php

namespace Core;
	
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\Pattern;
use Doctrine\Inflector\Rules\Patterns;
use Doctrine\Inflector\Rules\Ruleset;
use Doctrine\Inflector\Rules\Substitution;
use Doctrine\Inflector\Rules\Substitutions;
use Doctrine\Inflector\Rules\Transformation;
use Doctrine\Inflector\Rules\Transformations;
use Doctrine\Inflector\Rules\Word;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\Inflector\NoopWordInflector;

class Stringer{
      
      protected $inflector;

	  public function __construct()
	  {
	  	$inflector = InflectorFactory::create()
    ->withSingularRules(
        new Ruleset(
            new Transformations(
                new Transformation(new Pattern('/^(bil)er$/i'), '\1'),
                new Transformation(new Pattern('/^(inflec|contribu)tors$/i'), '\1ta')
            ),
            new Patterns(new Pattern('singulars')),
            new Substitutions(new Substitution(new Word('spins'), new Word('spinor')))
        )
    )
    ->withPluralRules(
        new Ruleset(
            new Transformations(
                new Transformation(new Pattern('^(bil)er$'), '\1'),
                new Transformation(new Pattern('^(inflec|contribu)tors$'), '\1ta')
            ),
            new Patterns(new Pattern('noflect'), new Pattern('abtuse')),
            new Substitutions(
                new Substitution(new Word('amaze'), new Word('amazable')),
                new Substitution(new Word('phone'), new Word('phonezes'))
            )
        )
    )
    ->build();

          $this->inflector=$inflector;

	  }

	  protected function pluralize($str)
	  {
	  	 return $this->inflector->pluralize($str);
	  }

	  protected function singularize($str)
	  {
	  	return $this->inflector->singularize($str);
	  }

	  protected function slug($str)
	  {
	  	 return  $this->inflector->urlize($str);
	  }

	  protected function removeAccent($str)
	  {
	  	  return  $this->inflector->unaccent($str);
	  }

	  protected function toTableName($str)
	  {
	  	return   $this->inflector->tableize($str);
	  }

	  protected function toModelName($str)
	  {
	  	return   $this->inflector->classify($str);
	  }

	  protected function camale($str)
	  {
	  	 return  $this->inflector->camelize($str); 
	  }

	  protected function reverse($str)
	  {
	  	 return text_rev($str);
	  }

     public static function __callStatic(string $method,array $params)
     {
     
	        if(method_exists(new static,$method))
	        { 
	            return (new static)->$method(...$params);
	        }
	        else
	        {
	             throw new Exception('The ' . $method . ' method is not supported.');
	        }
    
     }

}