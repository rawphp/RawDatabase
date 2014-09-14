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

/**
 * The database interface.
 * 
 * @category  PHP
 * @package   RawPHP/RawDatabase
 * @author    Tom Kaczohca <tom@rawphp.org>
 * @copyright 2014 Tom Kaczocha
 * @license   http://rawphp.org/license.txt MIT
 * @link      http://rawphp.org/
 */
interface IDatabase
{
    /**
     * Initialises the database.
     * 
     * @param array $config configuration array
     * 
     * @action ON_BEFORE_INIT_ACTION
     * @action ON_AFTER_INIT_ACTION
     */
    public function init( $config );
    
    /**
     * Returns the last mysql error.
     * 
     * @action ON_GET_ERROR_ACTION
     * 
     * @filter ON_GET_ERROR_FILTER(1)
     * 
     * @return string last error
     */
    public function getError();
    
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
    public function query( $query );
    
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
    public function insert( $query );
    
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
    public function execute( $query );
    
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
    public function modify( $query );
    
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
    public function tableExists( $table );
    
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
    public function truncateTable( $table );
    
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
    );
    
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
    public function dropTable( $table );
    
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
    public function addColumn( $table, $name, $type );
    
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
    public function dropColumn( $table, $name );
    
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
    public function addForeignKey( $table, $key = array() );
    
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
    public function dropForeignKey( $table, $keyName );
    
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
    public function getTableForeignKeys( $table );
    
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
    public function indexExists( $table, $columns = array() );
    
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
    public function addIndex( $table, $columns, $name = '', $type = self::INDEX_INDEX );
    
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
    public function getTableIndexes( $table );
    
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
    public function dropIndex( $table, $name );
    
    /**
     * Escapes a string and makes it ready to insert into the database.
     * 
     * @param string $string the string to prepare
     * 
     * @filter ON_PREPARE_STRING_FILTER(2)
     * 
     * @return string to prepared string
     */
    public function prepareString( $string );
    
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
    public function startTransaction( );
    
    /**
     * Commits a database transaction.
     * 
     * @action ON_COMMIT_TRANSACTION_ACTION
     * 
     * @filter ON_COMMIT_TRANSACTION_FILTER(1)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function commitTransaction( );
    
    /**
     * Reverses a database transaction.
     * 
     * @action ON_ROLLBACK_TRANSACTION_ACTION
     * 
     * @filter ON_ROLLBACK_TRANSACTION_FILTER(1)
     * 
     * @return bool TRUE on success, FALSE on failure
     */
    public function rollbackTransaction( );
    
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
    public function setTransactionAutoCommit( $autoCommit = FALSE );
    
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
    public function lockTables( $tables );
    
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
    public function unlockTables( );
}
