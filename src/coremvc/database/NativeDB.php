<?php
/**
 *  DB - A simple database class 
 * 
 * Core MVC
 *         
 * 
 *
 */
namespace Core\Database;

use pdo; 
use Core\Database\MigrationTrait;
use Core\Request;
use RuntimeException;
use AllowDynamicProperties;
#[AllowDynamicProperties]
class NativeDB
{
   
    use MigrationTrait;


    # @object, The PDO object
    protected $pdo;
    
    # @object, PDO statement object
    private   $sQuery;

    private   $tbl;

    private   $select;
    private   $where;
    private   $group_by;
    private   $having;
    private   $order_by;
    private   $limit;
    private   $offset;
    private   $join;
    private   $binding;
    private   $union;

    private   $builder;

    private   $where_binding=[];
    private   $having_binding=[];
    private   $querystring;

    private static $get_instance;
    private $total_pages;
    private $current_page;
    

    
/**
     *   Default Constructor 
     
     *  1. Connect to database.
    
     */
    public function __construct()
    {
        
        $this->Connect();
       
    }

    public static function getInstance()
    {
        if(!self::$get_instance)
        {
            self::$get_instance=new static;
        }
        return self::$get_instance;
    }
    
/**
     *  This method makes connection to the database.
     *  
     *  1. Reads the database settings from a config.php file. 
     *  2. Puts  the ini content into the settings array.
     *  3. Tries to connect to the database.
     *  4. If connection failed, exception is displayed 
     */
    private function Connect()
    {
      
        $dsn            =env('DB').':host='.env('HOSTNAME').';dbname='.env('DBNAME');
        try {
          
            $this->pdo = new PDO($dsn,env('USERNAME'),env('PASSWORD'), array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ));
            
            # We can now log any exceptions on Fatal error. 
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            # Disable emulation of prepared statements, use REAL prepared statements instead.
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            

            
        
        }
        catch (PDOException $e) {
            
                echo '<h1>Error:</h1>'.$e->getMessage();
            die();
        }
    }
     
/**
     *execute any Data from database  
     *@param $query string  //for sql statment
     *@param $data  array  // for prepared data
     *@return bolean
     */
    public function pdoSql($query,$data='')
    {
        //check data array
         if (!is_array($data)) {
            $data=(array) $data;
         }

       //prepare query 
     $prepare=$this->pdo->prepare($query);
         
         $this->sQuery=$prepare;
      
      $doquery=$prepare->execute($data);
      // echo $query;
         
       return true;

       }
    
/**
     * Inserting row into database
     * @param string $table
     * @param array $data
     * @return boolean
     */

    public function insert($table,$data)
     {
         
          // setup some variables for fields and values
        $fields  = '';
        $values = '';
        $data2=array(); // data paramters
        // populate them
        //if (is_array($data)) {
       // echo "1";
       // }
      
        foreach ($data as $f => $v)
        {
            $fields  .= "`$f`,";
            $values .= "?,";
            $data2[count($data2)]=$v;
        }

        // remove our trailing ','
        $fields = substr($fields, 0, -1);
        // remove our trailing ','
        $values = substr($values, 0, -1);
        
        $querystring = "INSERT INTO `{$table}` ({$fields}) VALUES({$values})";
               
        $this->querystring=$querystring;
        // print_r($data2);
       


        if($this->pdoSql($querystring,$data2))
            
         return TRUE;

        return FALSE;




     }

     public function paginate($per_page=10)
     {  
        $request = new Request;
        $page    = $request->has('page')?intval($request->get('page')):1;
        $offset  = ($page-1)*$per_page;

         $this->limit($per_page);
         $this->offset($offset);   

        $total_pages =  $this->query("SELECT COUNT(*) as total FROM $this->tbl")[0]->columns['total']??1;
         
        //dd($total_pages);
        $data         =  $this->run();
        
        
        
        $pages        = ($data)?ceil($total_pages/$per_page):1;
         

         $this->current_page = $page;
         $this->total_pages  = intval($pages);
        
       
         $links = ($data)?$this->links():null;

            return ['links'=>$links,$data??[]];
     }

     protected function nextPage()
     {
         $currentPage=$this->current_page;

         $nextPage = $currentPage + 1;
         if($currentPage < $this->total_pages)
         {
           $next=" <li class=\"page-item\"><a class=\"page-link\" href=\"?page=".$nextPage."\">Next</a></li>";
          return $next;
        }
        else
        {
             $next=" <li class=\"page-item disabled\"><a class=\"page-link\" href=\"#\">Next</a></li>";
          return $next;

        }

     }

     protected function previousPage()
     {
         $currentPage=$this->current_page;
      
         $previousPage = $currentPage - 1;
         if($currentPage > 1)
         {
           $previous=" <li class=\"page-item\"><a class=\"page-link\" href=\"?page=".$previousPage."\">Previous</a></li>";
           return $previous;
         } 
         else
         {
            $previous=" <li class=\"page-item disabled\"><a class=\"page-link\" href=\"#\">Previous</a></li>";
           return $previous;

         }

     }

     public function links()
     {
       $totalPages= $this->total_pages;
       $currentPage = $this->current_page;
       $pagination='';
       
        $adjacents = "2";
        $second_last = $totalPages - 1; // total pages minus 1
        
        $pagelink='<ul class="pagination justify-content-center">';

        $pagelink.=$this->previousPage();
        
         if ($totalPages <= 5){   
         for ($counter = 1; $counter <= $totalPages; $counter++){
         if ($counter == $currentPage) {
          $pagelink.= "<li class=\"page-item active\"> <a class=\"page-link\">".$counter."</a></li>";
         }else{
          $pagelink.= "<li class=\"page-item\"> <a class=\"page-link\" href=\"?page=".$counter."\">".$counter."</a></li>";
          }
         }
        }elseif ($totalPages > 5){
           if($currentPage <= 4) { 
            for ($counter = 1; $counter < 8; $counter++){ 
             if ($counter == $currentPage) {
                $pagelink.= "<li class=\"page-item active\"><a class=\"page-link\" href=\"?page=".$counter."\">".$counter."</a></li>";
              }else{
                   $pagelink.= "<li class=\"page-item\"> <a class=\"page-link\" href=\"?page=".$counter."\">".$counter."</a></li>";
              }
            }
        $pagelink.= "<li class=\"page-item\"><a class=\"page-link\" href=\"\">...</a></li>";

         $pagelink.= "<li class=\"page-item\"><a class=\"page-link\" href=\"?page=".$second_last."\">".$second_last."</a></li>";
         $pagelink.= "<li class=\"page-item\"><a class=\"page-link\" href=\"?page=".$totalPages."\">".$totalPages."</a></li>";

        }elseif($currentPage > 4 && $currentPage < $totalPages - 4) { 
         $pagelink.= "<li class=\"page-item\"> <a class=\"page-link\" href=\"?page=1\">1</a></li>";
         $pagelink.= "<li class=\"page-item\"> <a class=\"page-link\" href=\"?page=2\">2</a></li>";

        $pagelink.= "<li class=\"page-item\"><a class=\"page-link\" href=\"\">...</a></li>";
        for (
             $counter = $currentPage - $adjacents;
             $counter <= $currentPage + $adjacents;
             $counter++
             ) { 
             if ($counter == $currentPage) {
               $pagelink.= "<li class=\"page-item active\"><a class=\"page-link\">".$counter."</a></li>"; 
             }else{
                $pagelink.= "<li class=\"page-item\"> <a class=\"page-link\" href=\"?page=".$counter."\">".$counter."</a></li>";
             }                  
        }
      $pagelink.= "<li class=\"page-item\"><a class=\"page-link\" href=\"\">...</a></li>";

         $pagelink.= "<li class=\"page-item\"> <a class=\"page-link\" href=\"?page=".$second_last."\">".$second_last."</a></li>";
         $pagelink.= "<li class=\"page-item\"> <a class=\"page-link\" href=\"?page=".$totalPages."\">".$totalPages."</a></li>";
        }else {
         $pagelink.= "<li class=\"page-item\"><a class=\"page-link\" href=\"?page=1\">1</a></li>";
         $pagelink.= "<li class=\"page-item\"><a class=\"page-link\" href=\"?page=2\">2</a></li>";

         $pagelink.= "<li class=\"page-item\"><a class=\"page-link\" href=\"\">...</a></li>";
        
        for (
             $counter = $totalPages - 6;
             $counter <= $totalPages;
             $counter++
             ) {
             if ($counter == $currentPage) {
               $pagelink.= "<li class=\"page-item active\"><a class=\"page-link\">".$counter."</a></li>"; 
               }else{
                $pagelink.= "<li class=\"page-item\"><a class=\"page-link\" href=\"?page=".$counter."\">".$counter."</a><li>";
               }                   
        }}}
        
        $pagination.=$pagelink;
        $pagination.=$this->nextPage($totalPages);
        $pagination.='</ul>';
          
         // dd($pagination);
        return $pagination;


     }

     public function paginateCount()
     {
         return $this->total_pages;
     }

     public function paginateCurrentPage()
     {
         return $this->current_page;
     }



 /**
     * Delete row in database
     * @param string $from
     * @param array  $where
     * @return boolean
     */
    public function delete($from,array $where)
    {      

        $whereq='';     // WHERE query
        $whereword='';  // add 'WHERE' if where query exists
        $data=array(); // for exexcute query none ':'
          if (is_array($where))
        {
            $whereword.='WHERE';
          foreach ($where as $k => $val)
          {
            $whereq.=' '."$k"."=?".' ';
            $data[count($data)]="$val";
        }


        }



         $query ="DELETE FROM `$from` $whereword $whereq";
         //echo $query;
         $this->pdoSql($query,$data);
   
    }

/**  
     * Update row in database
     * @param string $table
     * @param array  $where
     * @param array  $data
     * @param string $whereoperator
     * @return boolean
     */

    public function update($table,$data,$where='',$whereoperator='',$soft_delete=false)
    {
        //set $key = $value :)
        
        $query  = '';
        $data2=array(); // for exexcute query none ':'
        $whereq='';     // WHERE query
        $whereword='';  // add 'WHERE' if where query exists
        $data3=array(); // for exexcute query none ':'
          if (is_array($where) && count($where)===1)
        {
            $whereword.='WHERE';
          foreach ($where as $k => $val) {
            $whereq.="$k"."=?".' ';
            $data3[count($data3)]=$val;
        }



        }
        elseif (is_array($where) && count($where)>1)
        {  
             $whereword.='WHERE';
          foreach ($where as $k => $val) {
            $whereq.="$k"."=?".' '.$whereoperator.' ';
            $data3[count($data3)]=$val;

           
        }   
            $whereq=substr($whereq, 0,(strlen($whereoperator)+1)*-1);
       }


        foreach ($data as $f => $v) {
           
            $query  .= "`$f` = ? ,";
            $data2[count($data2)]="$v";
        }
        
        //Remove trailing ,
        $query = substr($query, 0,-1);
        
        
        $querystring = "UPDATE `{$table}` SET {$query} {$whereword} {$whereq}";
       
       if($soft_delete)
       {
            $querystring = "UPDATE `{$table}` SET $soft_delete=now() {$whereword} {$whereq}";
       }
        //echo $querystring;
       
        $fullex=array_merge($data2,$data3);
       
      // dd($querystring);
         
         $this->querystring=$querystring;
 
       if($this->pdoSql($querystring,$fullex))
            
         return true;

        return false;

    }


/**
  * bulid the query
  * @param  String $query;
  * @param  Array  $data;
  * @return Query result;
  */


      public function query($query=null,$data=[])
      {
        if($query)
        { 
           $this->pdosql($query,$data);
            
           return $this->affectedRows()>0?$this->getRows():null;
        }
        if(!$this->tbl)
        {
             throw new RuntimeException('Unknown query table name in query bulider');
        }
             return null;  
            
      }

      public function select()
      {

        $select       = func_get_args();
        $select       = implode(',',$select);

          $this->select = $select;  
        
        return $this;
      }

      public function table($table)
      {
         $this->tbl= $table;
         return $this;
      }

      public function join($table,$first,$operator,$second,$type='JOIN')
      {
        if(!$this->join)
        {
          $this->join = " $type `$table` ON $first $operator $second ";
        }
        else
        {
          $this->join .= " $type `$table` ON $first $operator $second ";
        }

        return $this;
      }

      public function leftJoin($table,$first,$operator,$second)
      {
         $this->join($table,$first,$operator,$second,'LEFT JOIN');
         return $this;
      }

      public function RightJoin($table,$first,$operator,$second)
      {
         $this->join($table,$first,$operator,$second,'RIGHT JON');
         return $this;
      }

      public function innerJoin($table,$first,$operator,$second)
      {
         $this->join($table,$first,$operator,$second,'INNER JOIN');
         return $this;
      }


      public function where($column,$operator,$value,$type=null)
      {
          $bind=($value)?'?':'';
        //  dd($bind);
          $where='`'.$column.'`'.' '.$operator.$bind;
        if(!$this->where)
        {
            $statment=" WHERE ".$where;

        }
        else
        {
             if($type==null)
             {
                $statment=" AND ".$where.' ';
             }
             else
             {
                 $statment=" ".$type." ".$where;
             }
        }

         $this->where .= $statment;
         $this->where_binding[]=$value;
          
         return $this;

      }

      public function orWhere($column,$operator,$value)
      {
         $this->where($column,$operator,$value,'OR');

         return $this;

      }

      public function group_by()
      {
        $group_by = func_get_args();
        $group_by = ' GROUP BY '.implode(', ',$group_by).' ';
         
         $this->group_by=$group_by;
         
         return $this;

      }

      public function having($column,$operator,$value)
      {
        $having='`'.$column.'`'.$operator.'?';
        if(!$this->having)
        {
            $statment=" HAVING ".$having;

        }
        else
        {
            $statment=" AND ".$having;
        }

         $this->having .= $statment;
         $this->having_binding[]=$value;

         return $this;
      }

      public function orderBy($column,$type=null)
      {
        $sep =$this->order_by?' , ':' ORDER BY ';
        $type= ( strtoupper($type) !=null && in_array(strtoupper($type),['ASC','DESC']) )?strtoupper($type):'ASC';
        
        $statment=$sep.$column.' '.$type.' ';

        $this->order_by=$statment;

        return $this;
      }

      public function limit($limit)
      {
        $this->limit='LIMIT '.$limit.' ';

        return $this->limit;
      }

     public function offset($offset)
      {
        $this->offset='OFFSET '.$offset.' ';

        return $this->offset;
      }

      public function run()
      {

          $this->buildQuery($this->builder);
            
            //dd($this->builder);
            //dd($this->binding);
         $data = $this->query($this->builder,array_filter($this->binding));

         $this->queryReset();

            return $data;
       
      }

      public function buildQuery($query)
      {

        $query =' SELECT ';
        $query.=$this->select?:'*';
        $query.=' FROM '.$this->tbl;
        $query.=$this->join.' ';
        $query.=$this->union.' ';
        $query.=$this->where.' ';
        $query.=$this->group_by.' ';
        $query.=$this->having.' ';
        $query.=$this->order_by.' ';
        $query.=$this->limit.' ';
        $query.=$this->offset.' ';
         
         $this->builder =$query;

         $this->binding = array_merge($this->where_binding,$this->having_binding);
         
         return $this;
      }

      public function insertInto($data)
      {
          if($this->tbl)
          {
             return $this->Insert($this->tbl,$data);
          }
          return null;
      }

      public function deleteRow($data)
      {
          if($this->tbl)
          {
             return $this->delete($this->tbl,$data);
          }
          return null;
      }

      public function updateRow($data,$where='',$whereoperator='',$soft_delete=false)
      {
          if($this->tbl)
          {
             return $this->update($this->tbl,$data,$where,$whereoperator,$soft_delete);
          }
          return null;
      }

      public function prime($id=null)
      {
        if($id)
        {
             $this->where('id','=',$id);
        }
         $this->buildQuery($this->builder);

         $data = $this->query($this->builder,$this->binding);

          $this->queryReset();

          return $data[0]??null;
      }

      public function all()
      {
         $this->query(null);
         $data = $this->query($this->builder);
         $this->queryReset();
          return $data;
      }


      protected function queryReset()
      {
        $this->table='';
        $this->join='';
        $this->union='';
        $this->where='';
        $this->limit='';
        $this->offset='';
        $this->group_by='';
        $this->order_by='';
        $this->select='';
        $this->binding='';
        $this->builder='';
        $this->where_binding=[];
        $this->having_binding=[];
        $this->having='';

         
      }

      public function queryColumn($query,$data=[])
      {  
         $this->pdosql($query,$data);
         return $this->affectedRows()>0?$this->RowsColumn():null;
      }


/**
  *get rows from last query
  *@param string $option;
  *@return data result
  */
    public function getRows()
    {
       $result=array();
       $rows=$this->affectedRows();
      //$this->sQuery->setFetchMode(PDO::FETCH_CLASS,get_class($this));
       for ($i=0; $i <$rows; $i++)
       {   
          

          $result[]= $this->sQuery->fetchObject(get_class($this));
                       
       }
       
     
       if(count($result) <= 0)
       {
             return NULL;  
      
       }
      
        return $result;
         

    }


    public function RowsColumn()
    {
       $result=array();
       $rows=$this->affectedRows();
      //$this->sQuery->setFetchMode(PDO::FETCH_CLASS,get_class($this));
       for ($i=0; $i <$rows; $i++)
       {   
          
          $result[]= $this->sQuery->fetch(PDO::FETCH_COLUMN);
                       
       }
       
     
       if(count($result) <= 0)
       {
             return NULL;  
      
       }
      
        return $result;
         

    }

    public function RowsAssoc()
    {
       $result=array();
       $rows=$this->affectedRows();
      //$this->sQuery->setFetchMode(PDO::FETCH_CLASS,get_class($this));
       for ($i=0; $i <$rows; $i++)
       {   
          
          $result[]= $this->sQuery->fetch(PDO::FETCH_ASSOC);
                       
       }
       
     
       if(count($result) <= 0)
       {
             return NULL;  
      
       }
      
        return $result;
         

    }


//get one data row

    public function getRow()
    {
       
       
       $result=array();
       $rows=$this->affectedRows();
      
       for ($i=0; $i <$rows; $i++)
       { 
           $result[]= $fetch=$this->sQuery->fetch();
       }
       
     
       if(count($result) > 0)
       {
             return $result[0];          
      
       }
          return NULL;



    }


    
// Count Query Affected Rows    
   
    public function affectedRows()
    {    
        if ($this->sQuery != NULL){
          return $this->sQuery->rowCount(); 
        }
       
    }


// function returns id for last inserted record 

    public function lastId()
    {
        return $this->pdo->lastInsertId();
    }


   
}
