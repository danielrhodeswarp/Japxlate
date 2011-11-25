<?php

/**
 * @package    Japxlate (https://github.com/danielrhodeswarp/Japxlate)
 * @copyright  Copyright (c) 2011 Warp Asylum Ltd (UK).
 * @license    see LICENCE file in source code root folder     New BSD License
 */

class MySQL
{
	private static $link;
	
	//
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
	
	//
	public static function close()
	{
		self::$link->close();
	}
	
	//
	private static function handleError($sql)
	{
		
		/*
		if(some condition ie. "on development server")
		{
			echo '<div style="font-weight:bold; white-space:pre; border:1px dashed red;"><span style="color:blue;">' . $sql . '</span><br/>gave an error of: <span style="color:red;">' . self::$link->error . '</span></div>';
		}
		*/
				
		
	}
	
	//----MDB2-like interface methods--------
	
	//
	public static function query($sql)	//Return resultset object
	{
		$result = self::$link->query($sql);
		
		if(!$result)
		{
			self::handleError($sql);
		}
		
		return $result;
	}
	
	//
	public static function queryOne($sql)	//Return single data item
	{
		$sql = $sql . ' LIMIT 0,1';
		
		$result = self::$link->query($sql);
		
		if(!$result)
		{
			self::handleError($sql);
		}
		
		$row = $result->fetch_row();
		
		$result->close();
		
		return $row[0];
	}
	
	//
	public static function queryRow($sql)	//Return one row as an assoc array
	{
		$sql = $sql . ' LIMIT 0,1';
		
		$result = self::$link->query($sql);
		
		if(!$result)
		{
			self::handleError($sql);
		}
		
		$row = $result->fetch_assoc();
		
		$result->close();
		
		return $row;
	}
	
	//
	public static function queryAll($sql)	//Return *all* rows as an array of assoc arrays
	{
		$result = self::$link->query($sql, MYSQLI_USE_RESULT);
		
		if(!$result)
		{
			self::handleError($sql);
		}
		
		$all_rows = array();
		
		while(!is_null($row = $result->fetch_assoc()))
		{
			$all_rows[] = $row;
		}
		
		$result->close();
		
		return $all_rows;
	}
	
	public static function queryAllUsingFieldAsIndex($sql, $fieldname)
	{
		$result = self::$link->query($sql, MYSQLI_USE_RESULT);
		
		if(!$result)
		{
			self::handleError($sql);
		}
		
		$all_rows = array();
		
		while(!is_null($row = $result->fetch_assoc()))
		{
			$all_rows[$row[$fieldname]] = $row;
		}
		
		$result->close();
		
		return $all_rows;
	}
	
	public static function queryCol($sql)	//Return first column of results table
	{
		$tempResults = self::$link->query($sql);
		
		if(!$tempResults)
		{
			self::handleError($sql);
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
	
	//index will be the first (of the two) fields specified 
	public static function queryColWithIndex($sql)
	{
		$tempResults = self::$link->query($sql);
		
		if(!$tempResults)
		{
			self::handleError($sql);
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
	
	//
	public static function exec($sql)
	{
		$result = self::$link->query($sql);
		
		if(!$result)
		{
			self::handleError($sql);
		}
		
		return $result;
	}
	
	
	public static function lastInsertId()
	{
		return mysqli_insert_id(self::$link);
		//SELECT LAST_INSERT_ID()
	}
	
	public static function affectedRows()
	{
		return mysqli_affected_rows(self::$link);
	}
	
	//----Utility methods--------
	
	public static function esc($string)
	{
		return mysqli_real_escape_string(self::$link, $string);	//considers current charset and etc
	}
	
	//clone ONE given row of a DB table. Optionally then changing some bits
	//of the cloned row. returns the primary key of the cloned row.
	//$changeset format is array['field'] = alreadyDbSafeNewValueWithQuotesIfNecessary
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
