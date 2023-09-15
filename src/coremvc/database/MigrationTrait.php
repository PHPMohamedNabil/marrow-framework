<?php

namespace Core\Database;

trait MigrationTrait
{


    public function applyMigrations($down=false)
    {
    	
    	
         $this->createMigrationsTable();
 
         $new_migrations =[];
         
        $applyed_migrations = $this->getAppliedMigrations()??[];
            

         $files = scandir(APP.'migrations'.DS);

         
        $applyed_diff= array_diff($files,$applyed_migrations);

        foreach ($applyed_diff as $migration)
        {
        	 if($migration === '.' || $migration ==='..')
        	 {
        	 	 continue;
        	 }
              $c_name = 'App\Migrations\\'.explode('.',$migration)[0];

        	  $instance = new $c_name;
        	  
        	  $this->log('Applying migration...'.$migration);

        	 
        	  	$instance->up($this);
        	  

        	  $new_migrations[]=$migration;
        }

        if(count($new_migrations))
        {
        	 $this->saveMigrations($new_migrations);
        }
        else
        {
        	return $this->log('All migrations are applied'.date('Y-m-d h:i:s',time()));
        }
        
    
    }




    public function createMigrationsTable()
    { 
       return $this->queryColumn("CREATE TABLE IF NOT EXISTS migrations (
              
              id INT AUTO_INCREMENT PRIMARY KEY,
              migration VARCHAR(255),
              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
               
        ) ENGINE=INNODB;");

 
    }

    public function getAppliedMigrations()
    {
    	return $this->queryColumn('SELECT migration from migrations');
    }

    public function rollback($migration,$all=false)
    {  
        if($all==false)
        {
            clearstatcache();
           if(file_exists(APP.'migrations'.DS.$migration.'.php'))
           {

              $this->pdoSql('DELETE FROM migrations where migration=?',[$migration.'.php']);
             $this->roll($migration,$all);
             //unlink(APP.'migrations'.DS.$migration.'.php');
            return  $this->log('Migration rolledback:'.$migration);
           }
        }
        else
        {
               $this->roll($migration,$all); 
            return  $this->log('Migration rolledback:'.$migration);
        }

           

       clearstatcache();
    }

    private function roll($mig,$all=false)
    {
    	$rolled=[];

        $files = scandir(APP.'migrations'.DS);

          $this->log('rollingback...'.$mig);

        foreach ($files as $migration)
        {
        	 if($migration === '.' || $migration ==='..')
        	 {
        	 	 continue;
        	 }
              $c_name = 'App\Migrations\\'.explode('.',$migration)[0];

        	  $instance = new $c_name;
        	  
        	
               

        	  	if($all==false)
        	  	{
        	  	   if($mig.'.php' ==$migration)
        	  	   {

        	  		   $instance->down($this);
                       $rolled[]=$migration;

        	  	   }
                   else
                   {
                       continue;
                   }
        	  	}
        	  	else
        	  	{
                    
        	  		  $instance->down($this);
                        $rolled[]=$migration;
        	  	}
  	  
        	
        }
        
        if(count($rolled))
        {
             $this->deleteMigrations();
        }
        else
        {
            return $this->log('nothing to be rolled migration table is empty');
        }

    }

    public function saveMigrations(array $migrations)
    {

    	$marks = implode(',', array_map(fn($m)=>'(?)',$migrations));

       return $this->pdoSql("INSERT INTO migrations (migration) VALUES $marks",$migrations);

    }

    public function deleteMigrations()
    {
          return $this->pdoSql("TRUNCATE `migrations`",[]);
    }

    protected function log($message)
    {
    	 echo '['.date('Y-m-d H:i:s').'] - '.$message.PHP_EOL;
    }

}