<?php

/**
 * This file is part of RawPHP - a PHP Framework.
 * 
 * Copyright (c) 2014 RawPHP.org
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * 
 * PHP version 5.4
 * 
 * @category  PHP
 * @package   RawPHP/RawDatabase
 * @author    Tom Kaczohca <tom@rawphp.org>
 * @copyright 2014 Tom Kaczocha
 * @license   http://rawphp.org/license.txt MIT
 * @link      http://rawphp.org/
 */

namespace RawPHP\RawDatabase;

use mysqli;
use RawPHP\RawBase\Component;
use RawPHP\RawDatabase\IDatabase;

/**
 * The database class provides MySQL database services.
 * 
 * @category  PHP
 * @package   RawPHP/RawDatabase
 * @author    Tom Kaczohca <tom@rawphp.org>
 * @copyright 2014 Tom Kaczocha
 * @license   http://rawphp.org/license.txt MIT
 * @link      http://rawphp.org/
 */
class Database extends Component implements IDatabase
{
    private $_database;
    private $_host;
    private $_user;
    private $_password;
    public $mysql;
    
    protected $query;
    
    /**
     * Initialises the database.
     * 
     * @param array $config configuration array
     * 
     * @action ON_BEFORE_INIT_ACTION
     * @action ON_AFTER_INIT_ACTION
     */
    public function init( $config = NULL )
    {
        parent::init( $config );
        
        $this->doAction( self::ON_BEFORE_INIT_ACTION );
        
        if ( $config !== NULL )
        {
            $this->_host        = $config[ 'db_host' ];
            $this->_user        = $config[ 'db_user' ];
            $this->_password    = $config[ 'db_pass' ];
            $this->_database    = $config[ 'db_name' ];

            $this->_connect();
        }
        
        $this->doAction( self::ON_AFTER_INIT_ACTION );
    }
    
    /**
     * Connects to the database.
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    private function _connect()
    {
        $this->mysql = new mysqli( $this->_host, $this->_user, $this->_password, $this->_database );
        
        if ( $this->mysql->connect_error )
        {
            echo "Failed to connect to database.";
            
            return FALSE;
        }

        return TRUE;
    }
    
    /**
     * Returns the last mysql error.
     * 
     * @action ON_GET_ERROR_ACTION
     * 
     * @filter ON_GET_ERROR_FILTER(1)
     * 
     * @return string last error
     */
    public function getError()
    {
        $this->doAction( self::ON_GET_ERROR_ACTION );
        
        return $this->filter( self::ON_GET_ERROR_FILTER, $this->mysql->error );
    }
    
    /**
     * Call this function when you're expecting a result
     * from queries like SELECT.
     * 
     * @param string $query the query string
     * 
     * @action ON_QUERY_ACTION
     * 
     * @filter ON_QUERY_FILTER(2)
     * 
     * @return mixed list of results or FALSE
     */
    public function query( $query )
    {
        $this->query = $query;
        
        $result = $this->mysql->query( $this->query );
        
        if ( FALSE === $result || 0 === $result->num_rows )
        {
            return array();
        }
        
        $data = array();
        
        while ( $row = $result->fetch_assoc() )
        {
            $data[] = $row;
        }
        
        $result->free();
        
        $this->doAction( self::ON_QUERY_ACTION );
        
        return $this->filter( self::ON_QUERY_FILTER, $data, $query );
    }
    
    /**
     * Inserts a record into the database.
     * 
     * @param string $query the query string
     * 
     * @action ON_INSERT_ACTION
     * 
     * @filter ON_INSERT_FILTER(2)
     * 
     * @return mixed inserted ID on success, FALSE on failure
     */
    public function insert( $query )
    {
        $this->query = $query;
        
        $id = NULL;
        
        if ( FALSE !== $this->mysql->query( $this->query ) )
        {
            $id = $this->mysql->insert_id;
        }
        else
        {
            $id = FALSE;
        }
        
        $this->doAction( self::ON_INSERT_ACTION );
        
        return $this->filter( self::ON_INSERT_FILTER, $id, $query );
    }
    
    /**
     * Executes a database query which does not return a value.
     * 
     * @param string $query the query
     * 
     * @action ON_EXECUTE_ACTION
     * 
     * @filter ON_EXECUTE_FILTER(2)
     * 
     * @return int|bool number of affected rows on success, FALSE on failure
     */
    public function execute( $query )
    {
        $this->query = $query;
        
        $result = NULL;
        
        if ( FALSE !== ( $result = $this->mysql->query( $this->query ) ) )
        {
            $result = $this->mysql->affected_rows;
        }
        else
        {
            $result = FALSE;
        }
        
        $this->doAction( self::ON_EXECUTE_ACTION );
        
        return $this->filter( self::ON_EXECUTE_FILTER, $result, $query );
    }
    
    /**
     * Executes database modification query.
     * 
     * @param string $query the query
     * 
     * @action ON_MODIFY_ACTION
     * 
     * @filter ON_MODIFY_FILTER(2)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function modify( $query )
    {
        $this->query = $query;
        
        $result = $this->mysql->query( $this->query );
        
        $this->doAction( self::ON_MODIFY_ACTION );
        
        return $this->filter( self::ON_MODIFY_FILTER, $result, $query );
    }
    
    /**
     * Starts a database transaction.
     * 
     * By default, MYSQL Transactions are set to AUTO_COMMIT the queries if not
     * disabled. You can disable AUTO COMMIT by calling 
     * <code>setTransactionAutoCommit( FALSE )</code> after calling this method.
     * 
     * @action ON_START_TRANSACTION_ACTION
     * 
     * @filter ON_START_TRANSACTION_FILTER(1)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function startTransaction( )
    {
        $this->query = "START TRANSACTION;";
        
        $result =  $this->modify( $this->query );
        
        $this->doAction( self::ON_START_TRANSACTION_ACTION );
        
        return $this->filter( self::ON_START_TRANSACTION_FILTER, $result );
    }
    
    /**
     * Commits a database transaction.
     * 
     * @action ON_COMMIT_TRANSACTION_ACTION
     * 
     * @filter ON_COMMIT_TRANSACTION_FILTER(1)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function commitTransaction( )
    {
        $this->query = "COMMIT;";
        
        $result = $this->modify( $this->query );
        
        $this->doAction( self::ON_COMMIT_TRANSACTION_ACTION );
        
        return $this->filter( self::ON_COMMIT_TRANSACTION_FILTER, $result );
    }
    
    /**
     * Reverses a database transaction.
     * 
     * @action ON_ROLLBACK_TRANSACTION_ACTION
     * 
     * @filter ON_ROLLBACK_TRANSACTION_FILTER(1)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function rollbackTransaction( )
    {
        $this->query = "ROLLBACK;";
        
        $result = $this->modify( $this->query );
        
        $this->doAction( self::ON_ROLLBACK_TRANSACTION_ACTION );
        
        return $this->filter( self::ON_ROLLBACK_TRANSACTION_FILTER, $result );
    }
    
    /**
     * Sets the AUTO COMMIT option.
     * 
     * @param bool $autoCommit whether auto commit should be on
     * 
     * @action ON_SET_TRANSACTION_AUTO_COMMIT_ACTION
     * 
     * @filter ON_SET_TRANSACTION_AUTO_COMMIT_FILTER(2)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function setTransactionAutoCommit( $autoCommit = FALSE )
    {
        $this->query = "SET autocommit = 0;";
        
        $result = NULL;
        
        if ( !$autoCommit )
        {
            $result = $this->modify( $this->query );
        }
        
        $this->doAction( self::ON_SET_TRANSACTION_AUTO_COMMIT_ACTION );
        
        return $this->filter( self::ON_SET_TRANSACTION_AUTO_COMMIT_FILTER, $result, $autoCommit );
    }
    
    /**
     * Checks if a table exists.
     * 
     * @param string $table table name
     * 
     * @action ON_TABLE_EXISTS_ACTION
     * 
     * @filter ON_TABLE_EXISTS_FILTER(2)
     * 
     * @return bool TRUE if table exists, else FALSE
     */
    public function tableExists( $table )
    {
        $dbName = $this->_database;
        
        $query = "SELECT COUNT(*) AS count FROM information_schema.tables 
                  WHERE table_schema = '$dbName' AND table_name = '$table'";
        
        $results = $this->query( $query );
        
        $result = ( 0 < $results[ 0 ][ 'count' ] );
        
        $this->doAction( self::ON_TABLE_EXISTS_ACTION );
        
        return $this->filter( self::ON_TABLE_EXISTS_FILTER, $result, $table );
    }
    
    /**
     * Deletes all records from a table.
     * 
     * @param string $table table name
     * 
     * @action ON_BEFORE_TRUNCATE_TABLE_ACTION
     * @action ON_AFTER_TRUNCATE_TABLE_ACTION
     * 
     * @filter ON_TRUNCATE_TABLE_FILTER(2)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function truncateTable( $table )
    {
        $this->doAction( self::ON_BEFORE_TRUNCATE_TABLE_ACTION );
        
        $this->query = "TRUNCATE TABLE `$table`;";
        
        $result = $this->modify( $this->query );
        
        $this->doAction( self::ON_AFTER_TRUNCATE_TABLE_ACTION );
        
        return $this->filter( self::ON_TRUNCATE_TABLE_FILTER, $result, $table );
    }
    
    /**
     * Creates a database table.
     * 
     * @param string $name      table name
     * @param array  $columns   table column definitions
     * @param string $charSet   default character set - defaults to 'utf8'
     * @param string $engine    database engine - defaults to 'InnoDB'
     * @param string $collation default collation - defaults to 'utf8_unicode_ci'
     * 
     * @action ON_BEFORE_CREATE_TABLE_ACTION
     * @action ON_AFTER_CREATE_TABLE_ACTION
     * 
     * @filter ON_CREATE_TABLE_FILTER(6)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function createTable( $name, $columns = array(), $charSet = 'utf8', 
        $engine = 'InnoDB', $collation = 'utf8_unicode_ci'
    )
    {
        $this->doAction( self::ON_BEFORE_CREATE_TABLE_ACTION );
        
        $query = "CREATE TABLE IF NOT EXISTS `$name` ( ";
        
        foreach( $columns as $name => $type )
        {
            $query .= "`$name` $type, ";
        }
        
        $query = rtrim( $query, ', ' );
        
        $query .= " ) ";
        $query .= "ENGINE= $engine ";
        $query .= "DEFAULT CHARSET $charSet ";
        $query .= "COLLATE $collation;";
        
        $result = $this->modify( $query );
        
        $this->doAction( self::ON_AFTER_CREATE_TABLE_ACTION );
        
        return $this->filter( self::ON_CREATE_TABLE_FILTER, 
                $result, $name, $columns, $charSet, $engine, $collation
        );
    }
    
    /**
     * Deletes a table from the database.
     * 
     * @param string $table table name
     * 
     * @action ON_BEFORE_DROP_TABLE_ACTION
     * @action ON_AFTER_DROP_TABLE_ACTION
     * 
     * @filter ON_DROP_TABLE_FILTER(2)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function dropTable( $table )
    {
        $this->doAction( self::ON_BEFORE_DROP_TABLE_ACTION );
        
        $this->query = "DROP TABLE IF EXISTS `$table`;";
        
        $this->doAction( self::ON_AFTER_DROP_TABLE_ACTION );
        
        $result = $this->modify( $this->query );
        
        return $this->filter( self::ON_DROP_TABLE_FILTER, $result, $table );
    }
    
    /**
     * Adds a new column to a database table.
     * 
     * @param string $table table name
     * @param string $name  new column name
     * @param string $type  column type definition
     * 
     * @action ON_ADD_COLUMN_ACTION
     * 
     * @filter ON_ADD_COLUMN_FILTER(4)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function addColumn( $table, $name, $type )
    {
        $query = "ALTER TABLE `$table` ADD ";
        $query .= "`$name` $type;";
        
        $result = $this->modify( $query );
        
        $this->doAction( self::ON_ADD_COLUMN_ACTION );
        
        return $this->filter( self::ON_ADD_COLUMN_FILTER, $result, $table, $name, $type );
    }
    
    /**
     * Removes a column from a database table.
     * 
     * @param string $table table name
     * @param string $name  column name to drop
     * 
     * @action ON_DROP_COLUMN_ACTION
     * 
     * @filter ON_DROP_COLUMN_FILTER(3)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function dropColumn( $table, $name )
    {
        $query = "ALTER TABLE `$table` DROP COLUMN `$name`;";
        
        $result = $this->modify( $query );
        
        $this->doAction( self::ON_DROP_COLUMN_ACTION );
        
        return $this->filter( self::ON_DROP_COLUMN_FILTER, $result, $table, $name );
    }
    
    /**
     * Adds a foreign key to a table.
     * 
     * The key array should be in the following format:
     * 
     * @param string $table table name
     * @param array  $key   foreign key parameters
     * 
     * $key = array(
     *     'key_name'    => 'fk_self_table,
     *     'self_column' => 'order_id',
     *     'ref_table    => 'orders',
     *     'ref_column'  => 'order_id',
     *     'on_delete'   => SET NULL',
     *     'on_update'   => SET NULL',
     * );
     * 
     * @action ON_ADD_FOREIGN_KEY_ACTION
     * 
     * @filter ON_ADD_FOREIGN_KEY_FILTER(3)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function addForeignKey( $table, $key = array() )
    {
        $keyName  = $key[ 'key_name' ];
        $selfCol  = $key[ 'self_column' ];
        $refTable = $key[ 'ref_table'];
        $refCol   = $key[ 'ref_column' ];
        
        $query  = "ALTER TABLE `$table` ";
        $query .= "ADD CONSTRAINT `$keyName` FOREIGN KEY ";
        $query .= "( `$selfCol` ) REFERENCES `$refTable` ( `$refCol` )";
        
        if ( isset( $key[ 'on_delete' ] ) )
        {
            $delete = $key[ 'on_delete' ];
            
            $query .= "ON DELETE $delete";
        }
        
        if ( isset( $key[ 'on_update' ] ) )
        {
            $update = $key[ 'on_update' ];
            
            $query .= "ON UPDATE $update";
        }
        
        $query = trim( $query );
        $query .= ';';
        
        $result = $this->modify( $query );
        
        $this->doAction( self::ON_ADD_FOREIGN_KEY_ACTION );
        
        return $this->filter( self::ON_ADD_FOREIGN_KEY_FILTER, $result, $table, $key );
    }
    
    /**
     * Deletes a foreign key from a table.
     * 
     * @param string $table   table name
     * @param string $keyName key name
     * 
     * @action ON_BEFORE_DROP_FOREIGN_KEY_ACTION
     * @action ON_AFTER_DROP_FOREIGN_KEY_ACTION
     * 
     * @filter ON_DROP_FOREIGN_KEY_FILTER(3)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function dropForeignKey( $table, $keyName )
    {
        $this->doAction( self::ON_BEFORE_DROP_FOREIGN_KEY_ACTION );
        
        $query = "ALTER TABLE `$table` DROP FOREIGN KEY $keyName";
        
        $result = $this->modify( $query );
        
        $this->doAction( self::ON_AFTER_DROP_FOREIGN_KEY_ACTION );
        
        return $this->filter( self::ON_DROP_FOREIGN_KEY_FILTER, $result, $table, $keyName );
    }
    
    /**
     * Returns a list of foreign keys for a table.
     * 
     * @param string $table table name
     * 
     * @action ON_GET_TABLE_FOREIGN_KEYS_ACTION
     * 
     * @filter ON_GET_TABLE_FOREIGNKEYS_FILTER(2)
     * 
     * @return array list of foreign keys
     */
    public function getTableForeignKeys( $table )
    {
        $query = 'SELECT CONSTRAINT_SCHEMA, CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME,
                  REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                  FROM information_schema.KEY_COLUMN_USAGE WHERE `CONSTRAINT_SCHEMA` = \''
                  . $this->_database . '\' AND `TABLE_NAME` = \'' . $table . '\' AND 
                  `REFERENCED_COLUMN_NAME` IS NOT NULL;';
        
        $results = $this->query( $query );
        
        $keys = array();
        
        if ( !empty( $results ) )
        {
            foreach( $results as $result )
            {
                $record = array();
                $record[ 'database' ]            = $result[ 'CONSTRAINT_SCHEMA' ];
                $record[ 'key_name' ]            = $result[ 'CONSTRAINT_NAME' ];
                $record[ 'table' ]               = $result[ 'TABLE_NAME' ];
                $record[ 'column' ]              = $result[ 'COLUMN_NAME' ];
                $record[ 'referenced_database' ] = $result[ 'REFERENCED_TABLE_SCHEMA' ];
                $record[ 'referenced_table' ]    = $result[ 'REFERENCED_TABLE_NAME' ];
                $record[ 'referenced_column' ]   = $result[ 'REFERENCED_COLUMN_NAME' ];
                
                $keys[] = $record;
            }
        }
        
        $this->doAction( self::ON_GET_TABLE_FOREIGN_KEYS_ACTION );
        
        return $this->filter( self::ON_GET_TABLE_FOREIGNKEYS_FILTER, $keys, $table );
    }
    
    /**
     * Checks if an index exists on a table and its columns.
     * 
     * @param string $table   table name
     * @param array  $columns the table column names
     * 
     * @action ON_INDEX_ACTIONS_ACTION
     * 
     * @filter ON_INDEX_ACTIONS_FILTER(3)
     * 
     * @return bool TRUE if index exists, else FALSE
     */
    public function indexExists( $table, $columns = array() )
    {
        if ( !is_array( $columns ) )
        {
            throw new Exception( 'columns must be an array' );
        }
        
        $query = "SELECT * FROM information_schema.statistics WHERE table_schema =
                 '$this->_database' AND table_name = '$table' AND ";
        
        foreach( $columns as $column )
        {
            $query .= "column_name = '$column' OR ";
        }
        
        $query = rtrim( $query, ' OR ' );
        
        $result = $this->query( $query );
        
        $this->doAction( self::ON_INDEX_ACTIONS_ACTION );
        
        $result = !empty( $result );
        
        return $this->filter( self::ON_INDEX_ACTIONS_FILTER, $result, $table, $columns );
    }
    
    /**
     * Adds an index to a table.
     * 
     * @param string $table   table name
     * @param array  $columns list of columns
     * @param string $name    optional index name
     * @param string $type    type of index to create ( defaults to normal )
     * 
     * @action ON_ADD_INDEX_ACTION
     * 
     * @filter ON_ADD_INDEX_FILTER(5)
     * 
     * @return bool TRUE on success, FALSE on failure
     * 
     * @throws Exception if columns is not an array
     */
    public function addIndex( $table, $columns, $name = '', $type = self::INDEX_INDEX )
    {
        if ( !is_array( $columns ) )
        {
            throw new Exception( 'columns parameter must be an array' );
        }
        
        $query = "ALTER TABLE `$table` ADD $type $name ( ";
        
        foreach( $columns as $column )
        {
            $query .= "`$column`, ";
        }
        
        $query = rtrim( $query, ', ' );
        
        $query .= " )";
        
        $result = $this->modify( $query );
        
        $this->doAction( self::ON_ADD_INDEX_ACTION );
        
        return $this->filter( self::ON_ADD_INDEX_FILTER, 
                $result, $table, $columns, $name, $type
        );
    }
    
    /**
     * Returns a list of indexes for a table.
     * 
     * @param string $table table name
     * 
     * @action ON_GET_TABLE_INDEXES_ACTION
     * 
     * @filter ON_GET_TABLE_INDEXES_FILTER(2)
     * 
     * @return array list of indexes
     */
    public function getTableIndexes( $table )
    {
        $query = "SELECT * FROM information_schema.statistics WHERE table_schema =
                 '$this->_database' AND table_name = '$table'";
        
        $results = $this->query( $query );
        
        $indexes = array();
        
        if ( !empty( $results ) )
        {
            $name = NULL;
            
            $i = 0;
            
            foreach( $results as $result )
            {
                if ( NULL === $name )
                {
                    $name = $result[ 'INDEX_NAME' ];
                    $index = array();
                    $index[ $name ] = array();
                }
                
                $index[ $name ][] = $result[ 'COLUMN_NAME' ];
                
                $i++;
                
                if ( $i < count( $results ) && $name !== $results[ $i ][ 'INDEX_NAME' ] )
                {
                    $indexes[] = $index;
                    $name = NULL;
                }
                elseif ( $i === count( $results ) )
                {
                    $indexes[] = $index;
                }
            }
        }
        
        $this->doAction( self::ON_GET_TABLE_INDEXES_ACTION );
        
        return $this->filter( self::ON_GET_TABLE_INDEXES_FILTER, $indexes, $table );
    }
    
    /**
     * Drops an index from a table.
     * 
     * @param string $table table name
     * @param string $name  index name
     * 
     * @action ON_BEFORE_DROP_INDEX_ACTION
     * @action ON_AFTER_DROP_INDEX_ACTION
     * 
     * @filter ON_DROP_INDEX_FILTER(3)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function dropIndex( $table, $name )
    {
        $this->doAction( self::ON_BEFORE_DROP_INDEX_ACTION );
        
        $query = "ALTER TABLE `$table` DROP INDEX $name";
        
        $result = $this->modify( $query );
        
        $this->doAction( self::ON_AFTER_DROP_INDEX_ACTION );
        
        return $this->filter( self::ON_DROP_INDEX_FILTER, $result, $table, $name );
    }
    
    /**
     * Locks a table from being accessed by other clients.
     * 
     * NOTE: If you require locks on multiple tables at the same time, you need
     * to pass in all the table names in one call.
     * 
     * @param mixed $tables table names
     * 
     * @see http://dev.mysql.com/doc/refman/5.1/en/lock-tables.html
     * 
     * @action ON_BEFORE_LOCK_TABLES_ACTION
     * @action ON_AFTER_LOCK_TABLES_ACTION
     * 
     * @filter ON_LOCK_TABLES_FILTER(2)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function lockTables( $tables )
    {
        $this->doAction( self::ON_BEFORE_LOCK_TABLES_ACTION );
        
        if ( is_array( $tables ) )
        {
            $tmp = '';
            
            foreach( $tables as $table )
            {
                $tmp .= "$table, ";
            }
            
            $tmp = rtrim( $tmp, ', ' );
            
            $tables = $tmp;
        }
        
        $query = "LOCK TABLES $tables WRITE;";
        
        $result =  $this->modify( $query );
        
        $this->doAction( self::ON_AFTER_LOCK_TABLES_ACTION );
        
        return $this->filter( self::ON_LOCK_TABLES_FILTER, $result, $tables );
    }
    
    /**
     * Removes all locks that the program holds on the database.
     * 
     * @see http://dev.mysql.com/doc/refman/5.1/en/lock-tables.html
     * 
     * @action ON_BEFORE_UNLOCK_TABLES_ACTION
     * @action ON_AFTER_UNLOCK_TABLES_ACTION
     * 
     * @filter ON_UNLOCK_TABLES_FILTER(1)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function unlockTables( )
    {
        $this->doAction( self::ON_BEFORE_UNLOCK_TABLES_ACTION );
        
        $query = "UNLOCK TABLES;";
        
        $result = $this->modify( $query );
        
        $this->doAction( self::ON_AFTER_UNLOCK_TABLES_ACTION );
        
        return $this->filter( self::ON_UNLOCK_TABLES_FILTER, $result );
    }
    
    /**
     * Escapes a string and makes it ready to insert into the database.
     * 
     * @param string $string the string to prepare
     * 
     * @filter ON_PREPARE_STRING_FILTER(2)
     * 
     * @return string to prepared string
     */
    public function prepareString( $string )
    {
        $result = $this->mysql->real_escape_string( $string );
        
        return $this->filter( self::ON_PREPARE_STRING_FILTER, $result, $string );
    }
    
    
    const INDEX_INDEX       = 'INDEX';
    const INDEX_UNIQUE      = 'UNIQUE';
    const INDEX_PRIMARY_KEY = 'PRIMARY KEY';
    const INDEX_FULL_TEXT   = 'FULLTEXT';
    const INDEX_SPATIAL     = 'SPATIAL';
    
    // actions
    const ON_BEFORE_INIT_ACTION             = 'on_before_init_action';
    const ON_AFTER_INIT_ACTION              = 'on_after_init_action';
    
    const ON_GET_ERROR_ACTION               = 'on_get_error_action';
    
    const ON_QUERY_ACTION                   = 'on_query_action';
    const ON_INSERT_ACTION                  = 'on_insert_action';
    const ON_EXECUTE_ACTION                 = 'on_execute_action';
    const ON_MODIFY_ACTION                  = 'on_modify_action';
    
    const ON_START_TRANSACTION_ACTION       = 'on_start_transaction_action';
    const ON_COMMIT_TRANSACTION_ACTION      = 'on_commit_transaction_action';
    const ON_ROLLBACK_TRANSACTION_ACTION    = 'on_rollback_transaction_action';
    const ON_SET_TRANSACTION_AUTO_COMMIT_ACTION = 'on_set_transaction_auto_commit_action';
    
    const ON_TABLE_EXISTS_ACTION            = 'on_table_exists_action';
    const ON_BEFORE_TRUNCATE_TABLE_ACTION   = 'on_before_truncate_table';
    const ON_AFTER_TRUNCATE_TABLE_ACTION    = 'on_after_truncate_table';
    const ON_BEFORE_CREATE_TABLE_ACTION     = 'on_before_create_table_action';
    const ON_AFTER_CREATE_TABLE_ACTION      = 'on_after_create_table_action';
    const ON_BEFORE_DROP_TABLE_ACTION       = 'on_before_drop_table_action';
    const ON_AFTER_DROP_TABLE_ACTION        = 'on_after_drop_table_action';
    
    const ON_ADD_COLUMN_ACTION              = 'on_add_column_action';
    const ON_DROP_COLUMN_ACTION             = 'on_drop_column_action';
    
    const ON_ADD_FOREIGN_KEY_ACTION         = 'on_add_foreign_key_action';
    const ON_BEFORE_DROP_FOREIGN_KEY_ACTION = 'on_before_drop_foreign_key_action';
    const ON_AFTER_DROP_FOREIGN_KEY_ACTION  = 'on_after_drop_foreign_key_action';
    const ON_GET_TABLE_FOREIGN_KEYS_ACTION  = 'on_get_table_foreign_keys_action';
    
    const ON_INDEX_ACTIONS_ACTION           = 'on_index_exists_action';
    const ON_ADD_INDEX_ACTION               = 'on_add_index_action';
    const ON_GET_TABLE_INDEXES_ACTION       = 'on_get_table_indexes_action';
    const ON_BEFORE_DROP_INDEX_ACTION       = 'on_before_drop_index_action';
    const ON_AFTER_DROP_INDEX_ACTION        = 'on_after_drop_index_action';
    
    const ON_BEFORE_LOCK_TABLES_ACTION      = 'on_before_lock_tables_action';
    const ON_AFTER_LOCK_TABLES_ACTION       = 'on_after_lock_tables_action';
    const ON_BEFORE_UNLOCK_TABLES_ACTION    = 'on_before_unlock_tables_action';
    const ON_AFTER_UNLOCK_TABLES_ACTION     = 'on_after_unlock_tables_action';
    
    
    // filters
    const ON_GET_ERROR_FILTER               = 'on_get_error_filter';
    const ON_QUERY_FILTER                   = 'on_query_filter';
    const ON_INSERT_FILTER                  = 'on_insert_filter';
    const ON_EXECUTE_FILTER                 = 'on_execute_filter';
    const ON_MODIFY_FILTER                  = 'on_modify_filter';
    
    const ON_START_TRANSACTION_FILTER       = 'on_start_transaction_filter';
    const ON_COMMIT_TRANSACTION_FILTER      = 'on_commit_transaction_filter';
    const ON_ROLLBACK_TRANSACTION_FILTER    = 'on_rollback_transaction_filter';
    const ON_SET_TRANSACTION_AUTO_COMMIT_FILTER = 'on_set_transaction_auto_commit_filter';
    
    const ON_TABLE_EXISTS_FILTER            = 'on_table_exists_filter';
    const ON_TRUNCATE_TABLE_FILTER          = 'on_truncate_table_filter';
    const ON_CREATE_TABLE_FILTER            = 'on_create_table_filter';
    const ON_DROP_TABLE_FILTER              = 'on_drop_table_filter';
    
    const ON_ADD_COLUMN_FILTER              = 'on_add_column_filter';
    const ON_DROP_COLUMN_FILTER             = 'on_drop_column_filter';
    
    const ON_ADD_FOREIGN_KEY_FILTER         = 'on_add_foreign_key_filter';
    const ON_DROP_FOREIGN_KEY_FILTER        = 'on_drop_foreign_key_filter';
    const ON_GET_TABLE_FOREIGNKEYS_FILTER   = 'on_get_table_foreign_keys_filter';
    
    const ON_INDEX_ACTIONS_FILTER           = 'on_index_exists_filter';
    const ON_ADD_INDEX_FILTER               = 'on_add_index_filter';
    const ON_GET_TABLE_INDEXES_FILTER       = 'on_get_table_indexes_filter';
    const ON_DROP_INDEX_FILTER              = 'on_drop_index_filter';
    
    const ON_LOCK_TABLES_FILTER             = 'on_lock_tables_filter';
    const ON_UNLOCK_TABLES_FILTER           = 'on_unlock_tables_filter';
    
    const ON_PREPARE_STRING_FILTER          = 'on_prepare_string_filter';
}
