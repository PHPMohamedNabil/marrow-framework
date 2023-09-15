<?php

namespace Core;

use Core\Database\NativeDB;
use Core\Stringer;
use AllowDynamicProperties;

#[AllowDynamicProperties]
abstract class Model extends NativeDB implements \Countable{

      public    $table; //table name
      
      public $columns;
      
      protected $col_data;
      
      protected $jsonFileds;

      public  $incrementer=null;

      protected $mass;

      public function __construct($id=null)
      {
        parent::__construct();
        $this->table = (!isset($this->table))?$this->modelTableize():$this->table;
        $this->incrementer = $id;

      }

      public function get($id=null,$enable_soft_delete=false)
      {
        if(!$id && !$this->incrementer)
        {  

          if(isset($this->soft_delete) && $enable_soft_delete)
          { 
                
              $soft_del = $this->soft_delete[0];
              return $this->query("SELECT * FROM `$this->table` WHERE $soft_del  IS NULL");  
          }
        	return $this->query("SELECT * FROM `$this->table`");
        }
          //return dd($this->id);

        if($this->incrementer)
        {    
           $data = $this->query("SELECT * FROM `$this->table` WHERE id =?",[$this->incrementer]);
            if(isset($this->soft_delete) && $enable_soft_delete)
            {
               $soft_del = $this->soft_delete[0];
              $data = $this->query("SELECT * FROM `$this->table` WHERE id =? and $soft_del IS NULL",[$this->incrementer]);  
            }
           return ($data)?$data[0]:abort(404);
        }
             $data = $this->query("SELECT * FROM `$this->table` WHERE id =?",[$id]);
            if(isset($this->soft_delete) && $enable_soft_delete)
            {
              $soft_del = $this->soft_delete[0];
              $data = $this->query("SELECT * FROM `$this->table` WHERE id =? and $soft_del IS NULL",[$id]);  
            }

           return ($data)?$data:abort(404);

      }

      public function first($id,$enable_soft_delete=false)
      {
          $data = $this->query("SELECT * FROM `$this->table` WHERE id =?",[$id]);

          if(isset($this->soft_delete) && $enable_soft_delete)
          {  
            
            $soft_del = $this->soft_delete[0];

              $data = $this->query("SELECT * FROM `$this->table` WHERE id =? and  $soft_del IS NULL",[$id]);  
          }

           return ($data)?$data[0]:abort(404);
      }

      public function setJsonFiled()
      {
         //logic her upcoming version
      }

      public function getJsonFiled()
      {
        //logic her upcoming version
      }

      public function Jump($table,$t1_id,$t2_id,$type='JOIN')
      {
         $other_table = $this->table;

        return $this->query("SELECT t1.*,t2.* from $table t1 $type $other_table t2 ON t1.$t1_id=t2.$t2_id");
      }

      public function eager()
      {
         // write eager load logic her upcoming version
      }

      public function lazy()
      {
        // write eager load logic her upcoming version
        
      }

  

      public function deleteSoft($id=null)
      {  
        $soft_del =$this->soft_delete[0];
          return $this->update($this->table,[],['id'=>($this->incrementer)?$this->incrementer:$id],'',$soft_del);
      }

      public function purge($id=null)
      {
          return $this->delete($this->table,['id'=>($this->incrementer)?$this->incrementer:$id]);
      }

      public function amend($id=null)
      {
         return $this->update($this->table,$this->columns,['id'=>($this->incrementer)?$this->incrementer:$id]);
      }


      public function save()
      { 
         return $this->insert($this->table,$this->columns);
      }

      public function create($data)
      {
          $data = (is_array($data))?$data:[];
          $mass = isset($this->mass)?$this->mass:[];

          if(!$mass)
          {
             throw new \RuntimeException('No fillable data assignemt to model found while using mass assignment');
          }
             //dd($data);
          foreach ($data as $key => $value)
          {
                if(is_int($key))
                {
                    unset($data[$key]);
                }

                if(!in_array($key,$mass))
                {
                     unset($data[$key]);
                }

               
               
          }
        
            return $this->insert($this->table,$data);
          
      }


      //source :https://gist.github.com/adrienne/3180103

     public function haversine($lat,$lng,$radius,$table='',$extra = '',$extra_bind=[]) {

        $table = $this->table?$this->$table:$table;
        
        $output  = "SELECT distinct *, 
                    ( 3959 * acos( cos( radians( {$lat} ) ) * cos( radians( `latitude` ) ) * cos( radians( `longitude` ) - radians( ? ) ) + sin( radians( ? ) ) * sin( radians( `latitude` ) ) ) ) AS distance
                    FROM `{$table}` HAVING distance <= ? {$extra}
                    ORDER BY distance;";
                    
        return $this->query($output,array_filter(array_merge([$lat,$lng,$radius],$extra_bind)));
        } 

    public function modelTableize()
    {    
         $model_name =explode("\\",get_class($this));
         $model_name = end($model_name);

         $tbl_name = Stringer::toTableName($model_name);

         $plural   = Stringer::pluralize($tbl_name);
       
       
         
         return $plural;

    }


    
    public function __set($key,$value)
    {
          $this->columns[$key] = $value;     
    }
      
    public function __get($key)
    { 
        return isset($this->columns[$key])?$this->columns[$key]:null;

    }
      

    public function __toString()
    {
           
    }

    public function count(): int
    {
         return $this->affectedRows();
    }



}