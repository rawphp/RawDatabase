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
     */
    public function init( $config )
    {
        parent::init( $config );
        
        if ( $config !== NULL )
        {
            $this->_host        = $config[ 'db_host' ];
            $this->_user        = $config[ 'db_user' ];
            $this->_password    = $config[ 'db_pass' ];
            $this->_database    = $config[ 'db_name' ];

            $this->_connect();
        }
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
     * @return string last error
     */
    public function getError()
    {
        return $this->mysql->error;
    }

    /**
     * Call this function when you're expecting a result
     * from queries like SELECT.
     * 
     * @param string $query the query string
     * 
     * @return array|bool list of results or FALSE
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
        
        return $data;
    }

    /**
     * Inserts a record into the database.
     * 
     * @param string $query the query string
     * 
     * @return int|bool inserted ID on success, FALSE on failure
     */
    public function insert( $query )
    {
        $this->query = $query;
        
        if ( FALSE !== $this->mysql->query( $this->query ) )
        {
            return $this->mysql->insert_id;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Executes a database query which does not return a value.
     * 
     * @param string $query the query
     * 
     * @return int|bool number of affected rows on success, FALSE on failure
     */
    public function execute( $query )
    {
        $this->query = $query;
        
        if ( FALSE !== ( $result = $this->mysql->query( $this->query ) ) )
        {
            return $this->mysql->affected_rows;
        }
        else
        {
            return FALSE;
        }
    }
    
    /**
     * Executes database modification query.
     * 
     * @param string $query the query
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function modify( $query )
    {
        $this->query = $query;
        
        $result = $this->mysql->query( $this->query );
        
        return $result;
    }
    
    /**
     * Starts a database transaction.
     * 
     * By default, MYSQL Transactions are set to AUTO_COMMIT the queries if not
     * disabled. You can disable AUTO COMMIT by calling 
     * <code>setTransactionAutoCommit( FALSE )</code> after calling this method.
     */
    public function startTransaction( )
    {
        $this->query = "START TRANSACTION;";
        
        $this->execute( $this->query );
    }
    
    /**
     * Commits a database transaction.
     */
    public function commitTransaction( )
    {
        $this->query = "COMMIT;";
        
        $this->execute( $this->query );
    }
    
    /**
     * Reverses a database transaction.
     */
    public function rollbackTransaction( )
    {
        $this->query = "ROLLBACK;";
        
        $this->execute( $this->query );
    }
    
    /**
     * Sets the AUTO COMMIT option.
     * 
     * @param bool $autoCommit whether auto commit should be on
     */
    public function setTransactionAutoCommit( $autoCommit = FALSE )
    {
        $this->query = "SET autocommit = 0;";
        
        if ( !$autoCommit )
        {
            $this->execute( $this->query );
        }
    }
    
    /**
     * Checks if a table exists.
     * 
     * @param string $table table name
     * 
     * @return bool TRUE if table exists, else FALSE
     */
    public function tableExists( $table )
    {
        try
        {
            $this->query = "SELECT 1 FROM `$table` LIMIT 1";

            $this->execute( $this->query );

            $error = $this->getError();

            if ( FALSE !== strstr( $error, "doesn't exist" ) )
            {
                return FALSE;
            }

            return TRUE;
        }
        catch ( Exception $e )
        {
            return FALSE;
        }
    }
    
    /**
     * Deletes all records from a table.
     * 
     * @param string $table table name
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function truncateTable( $table )
    {
        $this->query = "TRUNCATE TABLE `$table`;";
        
        return $this->modify( $this->query );
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
     * @return bool TRUE on success, FALSE on failure
     */
    public function createTable( $name, $columns = array(), $charSet = 'utf8', 
        $engine = 'InnoDB', $collation = 'utf8_unicode_ci'
    )
    {
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
        
        if ( FALSE === $result )
        {
            throw new \Exception( 'Failed to create table: ' . $name );
        }
        
        return TRUE;
    }
    
    /**
     * Deletes a table from the database.
     * 
     * @param string $table table name
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function dropTable( $table )
    {
        $this->query = "DROP TABLE IF EXISTS `$table`;";
        
        return $this->modify( $this->query );
    }
    
    /**
     * Adds a new column to a database table.
     * 
     * @param string $table table name
     * @param string $name  new column name
     * @param string $type  column type definition
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function addColumn( $table, $name, $type )
    {
        $query = "ALTER TABLE `$table` ADD ";
        $query .= "`$name` $type;";
        
        return $this->modify( $query );
    }
    
    /**
     * Removes a column from a database table.
     * 
     * @param string $table table name
     * @param string $name  column name to drop
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function dropColumn( $table, $name )
    {
        $query = "ALTER TABLE `$table` DROP COLUMN `$name`;";
        
        return $this->modify( $query );
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
        
        return $this->modify( $query );
    }
    
    /**
     * Deletes a foreign key from a table.
     * 
     * @param string $table   table name
     * @param string $keyName key name
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function dropForeignKey( $table, $keyName )
    {
        $query = "ALTER TABLE `$table` DROP FOREIGN KEY $keyName";
        
        return $this->modify( $query );
    }
    
    /**
     * Returns a list of foreign keys for a table.
     * 
     * @param string $table table name
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
        
        return $keys;
    }
    
    /**
     * Checks if an index exists on a table and its columns.
     * 
     * @param string $table   table name
     * @param array  $columns the table column names
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
        
        return !empty( $result );
    }
    
    /**
     * Adds an index to a table.
     * 
     * @param string $table   table name
     * @param array  $columns list of columns
     * @param string $name    optional index name
     * @param string $type    type of index to create ( defaults to normal )
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
        
        return $this->modify( $query );
    }
    
    /**
     * Returns a list of indexes for a table.
     * 
     * @param string $table table name
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
        
        return $indexes;
    }
    
    /**
     * Drops an index from a table.
     * 
     * @param string $table table name
     * @param string $name  index name
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function dropIndex( $table, $name )
    {
        $query = "ALTER TABLE `$table` DROP INDEX $name";
        
        return $this->modify( $query );
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
     * @return bool TRUE on success, FALSE on failure
     */
    public function lockTables( $tables )
    {
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
        
        return $this->modify( $query );
    }
    
    /**
     * Removes all locks that the program holds on the database.
     * 
     * @see http://dev.mysql.com/doc/refman/5.1/en/lock-tables.html
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function unlockTables( )
    {
        $query = "UNLOCK TABLES;";
        
        return $this->modify( $query );
    }
    
    /**
     * Escapes a string and makes it ready to insert into the database.
     * 
     * @param string $string the string to prepare
     * 
     * @return string to prepared string
     */
    public function prepareString( $string )
    {
        return $this->mysql->real_escape_string( $string );
    }
    
    const INDEX_INDEX       = 'INDEX';
    const INDEX_UNIQUE      = 'UNIQUE';
    const INDEX_PRIMARY_KEY = 'PRIMARY KEY';
    const INDEX_FULL_TEXT   = 'FULLTEXT';
    const INDEX_SPATIAL     = 'SPATIAL';
}
