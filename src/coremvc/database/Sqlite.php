<?php

class Sqlite extends SQLite3{


	private $set; // array set values of query to be saved by create function

	private $get; // array get values
    
    public  $query; // query to be printed

    private $table; // table that used in query

    private $id; // The incrementer element 

    private $db=''; // db connection opend object;

    public function __construct($table='',$id=null,$connection_file='')
    { 
       try
       {
       	  //connectin to database
           $this->db = $this->open('database/todo.db');
           $this->enableExceptions(true);

           // registering the incrementer element to be used in insert and delete and update queries , create table prop if entered
           $this->id    = $id ?? '';
           $this->table = $table ?? '';

       }
       catch(Exception $e)
       {
           echo 'Caught exception: ' . $e->getMessage();
       }
      
	}

	
	public function list($table='tasks')
	{  
		$this->query = "SELECT * FROM $table";
       return $this->query("SELECT * FROM $table");
	}

	public function getOne($table='tasks',$where_data='',$fetch=true)
	{   
		$where='';
		if(!is_array($where_data))
		{
			 $where='id='.$this->id;

		}
		else{
			foreach ($where_data as $key => $value) {
			$where.= $key.'="'.$value.'"';
		   }
		
		}

		$this->query = "SELECT * FROM $table WHERE $where";

      return $fetch ? $this->query("SELECT * FROM $table WHERE $where")->fetchArray():$this->query("SELECT * FROM $table WHERE $where");
	}

	public function createTable($table="tasks")
	{
        return $this->exec("CREATE TABLE '$table'(
                 'id' INTEGER PRIMARY KEY AUTOINCREMENT,
                 'title' TEXT ,
                 'task_date' TEXT ,
                 'task_finished_date' TEXT ,
                 'is_notified' TEXT DEFAULT 0,
                 'is_done' TEXT DEFAULT 0,

                 )
        	");
	}

	public function save($table='tasks')
	{  
		$insert='';
		$keys  = '';
		foreach ($this->set as $key => $value) {
			$insert.= "'".$value."',";
			$keys  .= $key.',';
		}
		$insert = substr($insert,0,-1);
		$keys = substr($keys,0,-1);

		$this->query = "INSERT INTO $table ($keys) VALUES ($insert)";
		
        return $this->exec($this->query);
	}

	public function update($table='tasks',$id=null)
	{
		$update='';

		foreach ($this->set as $key => $value) {
			$update.= $key.'='."'".$value."',";
		}
		
		$update = substr($update,0,-1);
       
        if($id == null)
        {
        	$id    = $this->id;
        	$table = ($table)?$table:$this->table;
        }

		$this->query = "UPDATE $table SET $update WHERE id=$id";
        return $this->exec("UPDATE $table SET $update WHERE id=$id");
	}

	public function delete($table='tasks',$where)
	{
		$delete='';

		foreach ($where as $key => $value) {
			$delete.= $key.'='."'".$value."'AND ";
		}
		
		$delete = substr($delete,0,-4);
       
		$this->query = "DELETE FROM $table WHERE $delete";

        return $this->exec("DELETE FROM $table WHERE $delete");
	}
    
    //source https://www.php.net/manual/en/class.sqlite3result.php#115891

	public function numRows($result)
	{
	    $nrows = 0;
		$result->reset();
		while ($result->fetchArray())
		    $nrows++;
		$result->reset();
		return $nrows;
	}

	public function lastTouche()
	{
      return $this->changes();
	}

	public function __toString()
	{
		return json_encode($this->query);
	}

	public function __set($key,$value)
    {
        return $this->set[$key]=$value;
    }

    public function __get($key)
    {
        if(!isset($this->set[$key]))
        {
          return Null;	
        }
         return $this->set[$key];

    }

}

