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
 * PHP version 5.3
 * 
 * @category  PHP
 * @package   RawPHP/RawDatabase
 * @author    Tom Kaczohca <tom@rawphp.org>
 * @copyright 2014 Tom Kaczocha
 * @license   http://rawphp.org/license.txt MIT
 * @link      http://rawphp.org/
 */

namespace RawPHP\RawDatabase;

use RawPHP\RawBase\Exceptions\NotImplementedException;

/**
 * The database class provides MsSql database services.
 * 
 * @category  PHP
 * @package   RawPHP/RawDatabase
 * @author    Tom Kaczohca <tom@rawphp.org>
 * @copyright 2014 Tom Kaczocha
 * @license   http://rawphp.org/license.txt MIT
 * @link      http://rawphp.org/
 */
class MsSql extends Database
{
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
        throw new NotImplementedException( );
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
        throw new NotImplementedException( );
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
    public function addIndex( $table, $columns, $name = '',
        $type = self::INDEX_INDEX )
    {
        throw new NotImplementedException( );
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
    public function commitTransaction()
    {
        throw new NotImplementedException( );
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
        $engine = 'InnoDB', $collation = 'utf8_unicode_ci' )
    {
        throw new NotImplementedException( );
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
        throw new NotImplementedException( );
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
        throw new NotImplementedException( );
    }
    
    public function dropIndex( $table, $name )
    {
        throw new NotImplementedException( );
    }

    public function dropTable( $table )
    {
        throw new NotImplementedException( );
    }

    public function execute( $query )
    {
        throw new NotImplementedException( );
    }

    public function getError()
    {
        throw new NotImplementedException( );
    }

    public function getTableForeignKeys( $table )
    {
        throw new NotImplementedException( );
    }

    public function getTableIndexes( $table )
    {
        throw new NotImplementedException( );
    }

    public function indexExists( $table, $columns = array() )
    {
        throw new NotImplementedException( );
    }

    public function insert( $query )
    {
        throw new NotImplementedException( );
    }

    public function lockTables( $tables )
    {
        throw new NotImplementedException( );
    }

    public function modify( $query )
    {
        throw new NotImplementedException( );
    }

    public function prepareString( $string )
    {
        throw new NotImplementedException( );
    }

    public function query( $query )
    {
        throw new NotImplementedException( );
    }

    public function rollbackTransaction()
    {
        throw new NotImplementedException( );
    }

    public function setTransactionAutoCommit( $autoCommit = FALSE )
    {
        throw new NotImplementedException( );
    }

    public function startTransaction()
    {
        throw new NotImplementedException( );
    }

    public function tableExists( $table )
    {
        throw new NotImplementedException( );
    }

    public function truncateTable( $table )
    {
        throw new NotImplementedException( );
    }

    public function unlockTables()
    {
        throw new NotImplementedException( );
    }
}