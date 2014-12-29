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
 * @package   RawPHP\RawDatabase
 * @author    Tom Kaczocha <tom@rawphp.org>
 * @copyright 2014 Tom Kaczocha
 * @license   http://rawphp.org/license.txt MIT
 * @link      http://rawphp.org/
 */

namespace RawPHP\RawDatabase;

use Exception;
use RawPHP\RawDatabase\Contract\IDatabase;
use RawPHP\RawDatabase\Exception\DatabaseException;
use RawPHP\RawDatabase\Handler\Handler;
use RawPHP\RawDatabase\Handler\MsSql;
use RawPHP\RawDatabase\Handler\MySql;
use RawPHP\RawDatabase\Handler\SQLite;

/**
 * Base database class to be extended by service providers.
 *
 * @category  PHP
 * @package   RawPHP\RawDatabase
 * @author    Tom Kaczocha <tom@rawphp.org>
 * @copyright 2014 Tom Kaczocha
 * @license   http://rawphp.org/license.txt MIT
 * @link      http://rawphp.org/
 */
class Database implements IDatabase
{
    /** @var  Handler */
    protected $handler;

    /**
     * Create a new database instance.
     *
     * @param array $config
     *
     * @throws DatabaseException
     */
    public function __construct( array $config = [ ] )
    {
        if ( !isset( $config[ 'handler' ] ) )
        {
            throw new DatabaseException( "Missing 'handler' configuration." );
        }

        switch ( $config[ 'handler' ] )
        {
            case 'mssql':
                $this->handler = new MsSql();
                break;
            case 'sqlite':
                $this->handler = new SQLite();
                break;
            case 'mysql':
            default:
                $this->handler = new MySql();
                break;
        }

        $this->connect( $config );
    }

    /**
     * Close the database connection.
     *
     * @throws DatabaseException
     */
    public function close()
    {
        $this->handler->close();
    }

    /**
     * Get last executed query.
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->handler->getQuery();
    }

    /**
     * Call this function when you're expecting a result
     * from queries like SELECT.
     *
     * @param string $query the query string
     * @param array  $data
     *
     * @return mixed list of results or FALSE
     */
    public function query( $query, array $data = [ ] )
    {
        return $this->handler->query( $query, $data );
    }

    /**
     * Inserts a record into the database.
     *
     * @param string $query the query string
     * @param array  $data
     *
     * @return mixed inserted ID on success, FALSE on failure
     */
    public function insert( $query, array $data = [ ] )
    {
        return $this->handler->insert( $query, $data );
    }

    /**
     * Executes a database query which does not return a value.
     *
     * @param string $query the query
     * @param array  $data
     *
     * @return bool|int number of affected rows on success, FALSE on failure
     */
    public function execute( $query, array $data = [ ] )
    {
        return $this->handler->execute( $query, $data );
    }

    /**
     * Executes database modification query.
     *
     * @param string $query the query
     * @param array  $data
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function modify( $query, array $data = [ ] )
    {
        return $this->handler->modify( $query, $data );
    }

    /**
     * Initialises the database and creates a new
     * connection.
     *
     * @param array $config configuration array
     *
     * @throws DatabaseException
     */
    public function connect( $config )
    {
        $this->handler->connect( $config );
    }

    /**
     * Starts a database transaction.
     *
     * By default, MYSQL Transactions are set to AUTO_COMMIT the queries if not
     * disabled. You can disable AUTO COMMIT by calling
     * <code>setTransactionAutoCommit( FALSE )</code> after calling this method.
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function startTransaction()
    {
        $this->handler->startTransaction();
    }

    /**
     * Commits a database transaction.
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function commitTransaction()
    {
        $this->handler->commitTransaction();
    }

    /**
     * Reverses a database transaction.
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function rollbackTransaction()
    {
        $this->handler->rollbackTransaction();
    }

    /**
     * Sets the AUTO COMMIT option.
     *
     * @param bool $autoCommit whether auto commit should be on
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function setTransactionAutoCommit( $autoCommit = FALSE )
    {
        $this->handler->setTransactionAutoCommit( $autoCommit );
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
        return $this->handler->tableExists( $table );
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
        return $this->handler->truncateTable( $table );
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
    public function createTable( $name, $columns = [ ], $charSet = 'utf8',
                                 $engine = 'InnoDB', $collation = 'utf8_unicode_ci'
    )
    {
        return $this->handler->createTable( $name, $columns, $charSet, $engine, $collation );
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
        return $this->handler->dropTable( $table );
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
        return $this->handler->addColumn( $table, $name, $type );
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
        return $this->handler->dropColumn( $table, $name );
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
    public function addForeignKey( $table, $key = [ ] )
    {
        return $this->handler->addForeignKey( $table, $key );
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
        return $this->handler->dropForeignKey( $table, $keyName );
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
        return $this->handler->getTableForeignKeys( $table );
    }

    /**
     * Checks if an index exists on a table and its columns.
     *
     * @param string $table   table name
     * @param array  $columns the table column names
     *
     * @return bool TRUE if index exists, else FALSE
     */
    public function indexExists( $table, $columns = [ ] )
    {
        return $this->handler->indexExists( $table, $columns );
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
    public function addIndex( $table, $columns, $name = '', $type = IDatabase::INDEX_INDEX )
    {
        return $this->handler->addIndex( $table, $columns, $name, $type );
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
        return $this->handler->getTableIndexes( $table );
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
        return $this->handler->dropIndex( $table, $name );
    }

    /**
     * Locks a table from being accessed by other clients.
     *
     * NOTE: If you require locks on multiple tables at the same time, you need
     * to pass in all the table names in one call.
     *
     * @param mixed $tables table names
     *
     * @see    http://dev.mysql.com/doc/refman/5.1/en/lock-tables.html
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function lockTables( $tables )
    {
        return $this->handler->lockTables( $tables );
    }

    /**
     * Removes all locks that the program holds on the database.
     *
     * @see    http://dev.mysql.com/doc/refman/5.1/en/lock-tables.html
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function unlockTables()
    {
        return $this->handler->unlockTables();
    }
}