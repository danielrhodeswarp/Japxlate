<?php

/**
 * Singleton type interface to MySQL
 * 
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @author     Daniel Rhodes
 * @copyright  Copyright (c) 2011-2013 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */
class MySQL
{
    /**
     * The MySQL connection
     */
	private static $link;
	
	/**
     * Connect to specified MySQL database (else bomb out)
     * 
     * @author Daniel Rhodes
     * 
     * @param string $user database user
     * @param string $password database user's password
     * @param string $host database host
     * @param string $schema database schema name
     * @param bool $using_unicode optional. default true. set to true if you want to connect to MySQL in unicode mode
     */
	public static function connect($user, $password, $host, $schema, $using_unicode = true)
	{
		self::$link = new mysqli($host, $user, $password, $schema);
		
		/*
		if(!self::$connection)
		{
			die('Database disaster!');
		}
		*/
		
		/* check connection */
		if (mysqli_connect_errno()) {
		    printf("Connect failed: %s\n", mysqli_connect_error());
		    exit();
		}
		
		//VERY important for MySQL UTF-8 databases!
		if($using_unicode)
		{
			self::$link->query('SET NAMES utf8');
		}
		
		//self::$link->query('SET CHARACTER SET utf8');

		//set the default fetchmode
		//$this->connection->setFetchMode(MDB2_FETCHMODE_ASSOC);
	}
	
	/**
     * Close the MySQL connection
     * 
     * @author Daniel Rhodes
     */
	public static function close()
	{
		self::$link->close();
	}
	
	/**
     * Handle an SQL / DB error (eg. by printing or logging it)
     * 
     * @author Daniel Rhodes
     * 
     * @param string $sql the SQL that has failed
     */
	private static function handleError($sql)
	{
		
		/*
		if(some condition ie. "on development server")
		{
			echo '<div style="font-weight:bold; white-space:pre; border:1px dashed red;"><span style="color:blue;">' . $sql . '</span><br/>gave an error of: <span style="color:red;">' . self::$link->error . '</span></div>';
		}
		*/
		
		log_error($sql);
	}
	
	//----MDB2-like interface methods-------------------------------------------
	
	/**
     * Query the database with some "SELECT ..." type SQL
     * 
     * @author Daniel Rhodes
     * 
     * @param string $sql the SQL to run
     * @return type resultset object
     */
	public static function query($sql)
	{
		$result = self::$link->query($sql);
		
		if(!$result)
		{
			self::handleError($sql);
            //TODO should probably return here
		}
		
		return $result;
	}
	
	/**
     * Query the database with some "SELECT ..." type SQL and return the first
     * column value of the first returned row
     * 
     * @author Daniel Rhodes
     * 
     * @param string $sql the SQL to run
     * @return mixed single data item
     */
	public static function queryOne($sql)
	{
		$sql = $sql . ' LIMIT 0,1'; //TODO check not already in $sql!
		
		$result = self::$link->query($sql);
		
		if(!$result)
		{
			self::handleError($sql);
            //TODO should probably return here
		}
		
		$row = $result->fetch_row();
		
		$result->close();
		
		return $row[0];
	}
	
	/**
     * Query the database with some "SELECT ..." type SQL and return the first
     * returned row (as an assoc array)
     * 
     * @author Daniel Rhodes
     * 
     * @param string $sql the SQL to run
     * @return array one row as an assoc array
     */
	public static function queryRow($sql)
	{
		$sql = $sql . ' LIMIT 0,1'; //TODO check not already in $sql!
		
		$result = self::$link->query($sql);
		
		if(!$result)
		{
			self::handleError($sql);
            //TODO should probably return here
		}
		
		$row = $result->fetch_assoc();
		
		$result->close();
		
		return $row;
	}
	
	/**
     * Query the database with some "SELECT ..." type SQL and return *all*
     * returned rows (as an array of assoc arrays)
     * 
     * @author Daniel Rhodes
     * @note if the resultset is massive, you are going to run out of memory!
     * 
     * @param string $sql the SQL to run
     * @return array[] *all* rows as an array of assoc arrays
     */
	public static function queryAll($sql)
	{
		$result = self::$link->query($sql, MYSQLI_USE_RESULT);
		
		if(!$result)
		{
			self::handleError($sql);
            //TODO should probably return here
		}
		
		$all_rows = array();
		
		while(!is_null($row = $result->fetch_assoc()))
		{
			$all_rows[] = $row;
		}
		
		$result->close();
		
		return $all_rows;
	}
	
    /**
     * Query the database with some "SELECT ..." type SQL and return *all*
     * returned rows (as a custom-indexed array of assoc arrays) 
     * 
     * @author Daniel Rhodes
     * @note if the resultset is massive, you are going to run out of memory!
     * 
     * @param string $sql the SQL to run
     * @param string $fieldname the column whose value to use as an index for that row
     * @return array[] *all* rows as an array of assoc arrays. array index will be as per $fieldname's value for that row
     */
	public static function queryAllUsingFieldAsIndex($sql, $fieldname)
	{
		$result = self::$link->query($sql, MYSQLI_USE_RESULT);
		
		if(!$result)
		{
			self::handleError($sql);
            //TODO should probably return here
		}
		
		$all_rows = array();
		
		while(!is_null($row = $result->fetch_assoc()))
		{
			$all_rows[$row[$fieldname]] = $row;
		}
		
		$result->close();
		
		return $all_rows;
	}
	
    /**
     * Query the database with some "SELECT ..." type SQL and return the first
     * COLUMN of the resultset
     * 
     * @author Daniel Rhodes
     * 
     * @param string $sql the SQL to run
     * @return array first column of results table
     */
	public static function queryCol($sql)
	{
		$tempResults = self::$link->query($sql);
		
		if(!$tempResults)
		{
			self::handleError($sql);
            //TODO should probably return here
		}
		
		$column = array();
		
		while(!is_null($row = $tempResults->fetch_row()))
		{
			$column[] = $row[0];
		}
		
		$tempResults->close();
		
		//return $tempResults->fetchCol();
		return $column;
	}
	
	/**
     * Query the database with some "SELECT ..." type SQL and return a column
     * whose index is the forst field specified, and whose corresponding value
     * is the second field specified
     * 
     * @author Daniel Rhodes
     * 
     * @note index will be the first (of the two) fields specified
     * @note only the first two fields mentioned will be used to make the result
     * 
     * @param string $sql the SQL to run
     * @return array first column of results table
     */
	public static function queryColWithIndex($sql)
	{
		$tempResults = self::$link->query($sql);
		
		if(!$tempResults)
		{
			self::handleError($sql);
            //TODO should probably return here
		}
		
		$column = array();
		
		while(!is_null($row = $tempResults->fetch_row()))
		{
			$column[$row[0]] = $row[1];
		}
		
		$tempResults->close();
		
		//return $tempResults->fetchCol();
		return $column;
	}
	
	/**
     * Query the database with any old SQL (DML or DDL)
     * 
     * @author Daniel Rhodes
     * 
     * @param string $sql the SQL to run
     * @return type resultset object
     */
	public static function exec($sql)
	{
		$result = self::$link->query($sql);
		
		if(!$result)
		{
			self::handleError($sql);
            //TODO should probably return here
		}
		
		return $result;
	}
	
	/**
     * Get the value of the AUTO_INCREMENT field that was updated by the previous query
     * 
     * @author Daniel Rhodes
     * 
     * @return int|string last INSERTed value (as string numeral if greater than maximal int value)
     */
	public static function lastInsertId()
	{
		return mysqli_insert_id(self::$link);
		//SELECT LAST_INSERT_ID()
	}
	
    /**
     * Get the number of affected rows in a previous MySQL operation
     * 
     * @author Daniel Rhodes
     * 
     * @return int|string affected rows (as string numeral if greater than maximal int value)
     */
	public static function affectedRows()
	{
		return mysqli_affected_rows(self::$link);
	}
	
	//----Utility methods-------------------------------------------------------
	
    /**
     * Ensafen a string so as to use it as an INSERT value or what-have-you
     * 
     * @author Daniel Rhodes
     * @note this method considers the current DB charset and etc
     * 
     * @param string $string the source string to escape
     * @return string escaped $string as safe for INSERTing etc
     */
	public static function esc($string)
	{
		return mysqli_real_escape_string(self::$link, $string);
	}
	
	/**
     * clone ONE given row of a DB table (that has auto_inc pk). Optionally then
     * changing some bits of the cloned row. returns the primary key of the
     * cloned row.
     * 
     * @author Daniel Rhodes
     * 
     * @param string $table table name
     * @param array $fields columns from $table to clone into the new row (DON'T include the pk)
     * @param string $where_clause a WHERE clause to select a source row (ie. probably using the pk)
     * @param array $changeset optional. default null. changes to apply to cloned row after cloning. format is array['field'] = alreadyDbSafeNewValueWithQuotesIfNecessary
     * @param string $primary_key_field optional. default 'id'. name of $table's pk field. used only if a $changeset if provided
     * @return int|string pk of cloned row as per self::lastInsertId()
     */
    public static function cloneRecord($table, array $fields, $where_clause, array $changeset = null, $primary_key_field = 'id')
	{
		$fields_list = implode(',', $fields);
		
		$create_sql = <<<SQL
INSERT INTO {$table}({$fields_list})
SELECT {$fields_list} FROM {$table}
WHERE {$where_clause}
SQL;
		
		self::exec($create_sql);
		
		$cloned_row_id = self::lastInsertId();
		
		if(!is_null($changeset))
		{
			$change_list_parts = array();
			
			foreach($changeset as $field => $value)
			{
				$change_list_parts[] = "{$field} = {$value}";
			}
			
			$change_list = implode(',', $change_list_parts);
			
			$change_sql = <<<SQL
UPDATE {$table} SET {$change_list}
WHERE {$primary_key_field} = {$cloned_row_id}
SQL;
		
			MySQL::exec($change_sql);
		}
		
		return $cloned_row_id;
	}
	
}
