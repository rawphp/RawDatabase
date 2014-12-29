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
 * @package   RawPHP\RawDatabase\Handler
 * @author    Tom Kaczocha <tom@rawphp.org>
 * @copyright 2014 Tom Kaczocha
 * @license   http://rawphp.org/license.txt MIT
 * @link      http://rawphp.org/
 */

namespace RawPHP\RawDatabase\Handler;

use Exception;
use PDO;
use RawPHP\RawDatabase\Contract\IDatabase;
use RawPHP\RawDatabase\Exception\DatabaseException;
use RawPHP\RawSupport\Exception\NotImplementedException;

/**
 * Class SQLite
 *
 * @package RawPHP\RawDatabase\Handler
 */
class SQLite extends Handler
{
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
        if ( $config !== NULL )
        {
            if ( NULL === $config[ 'db_name' ] || '' === $config[ 'db_name' ] )
            {
                throw new DatabaseException( 'Invalid database name.' );
            }

            $this->name = $config[ 'db_name' ];

            try
            {
                $dns = "sqlite:" . $this->name;

                $this->database = new PDO( $dns );

            }
            catch ( Exception $e )
            {
                throw new DatabaseException( $e->getMessage() );
            }
        }
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
        throw new NotImplementedException();
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
        $query = "SELECT name FROM sqlite_master WHERE type='table' AND name='$table'";

        $statement = $this->database->query( $query );

        return ( 0 !== count( $statement->fetchAll() ) );
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
        $query = "DELETE FROM $table";

        $result = $this->execute( $query );

        if ( $result > 0 )
        {
            $this->execute( 'VACUUM' );

            return TRUE;
        }

        return FALSE;
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
        $query = "CREATE TABLE $name ( ";

        foreach ( $columns as $name => $type )
        {
            $query .= "$name $type, ";
        }

        $query = rtrim( $query, ', ' );

        $query .= " ) ";

        return $this->modify( $query );
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
        $query = "DROP TABLE $table";

        $result = $this->execute( $query );

        return ( $result > 0 );
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
        $query = "ALTER TABLE $table ADD COLUMN $name $type";

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
        try
        {
            $tmpTable = $table . '_old';

            $this->startTransaction();

            $this->copyTable( $table, $tmpTable, [ $name ] );

            $this->dropTable( $tmpTable );

            $this->commitTransaction();
        }
        catch ( Exception $e )
        {
            $this->rollbackTransaction();

            return FALSE;
        }

        return TRUE;
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
        $keyName  = $key[ 'key_name' ];
        $selfCol  = $key[ 'self_column' ];
        $refTable = $key[ 'ref_table' ];
        $refCol   = $key[ 'ref_column' ];

        $tmpName = $table . '_temp';

        try
        {
            $this->startTransaction();

            $this->copyTable( $table, $tmpName );

            $this->dropTable( $table );

            $columns = $this->getTableColumns( $tmpName );

            $query = "CREATE TABLE $table ( ";

            foreach ( $columns as $name => $type )
            {
                $query .= "$name $type, ";
            }

            $query .= "FOREIGN KEY( $selfCol ) REFERENCES $refTable( $refCol )";

            $query = trim( $query );

            $query .= " );";

            $this->modify( $query );

            // add existing data to new table
            $this->copyTableData( $tmpName, $table, $columns );

            $this->dropTable( $tmpName );

            $this->commitTransaction();
        }
        catch ( Exception $e )
        {
            $this->rollbackTransaction();

            return FALSE;
        }

        return TRUE;
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
        try
        {
            $tmpTable = $table . '_temp';

            $this->getTableForeignKeys( $table );

            $this->startTransaction();

            $this->copyTable( $table, $tmpTable );

            $this->dropTable( $table );

            $columns = $this->getTableColumns( $tmpTable );

            $this->createTable( $table, $columns );

            $this->copyTableData( $tmpTable, $table, $columns );

            $this->commitTransaction();
        }
        catch ( Exception $e )
        {
            $this->rollbackTransaction();

            return FALSE;
        }

        return TRUE;
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
        throw new NotImplementedException();
        
//        $query = "pragma table_info( $table )";
//
//        $result = $this->query( $query );

        $query = "SELECT * FROM sqlite_master";

        $results = $this->query( $query );

        //print_r( $results );
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
        $indexes = $this->getTableIndexes( $table );

        $name = '';

        $this->prepareIndexName( $columns, $name );

        foreach ( $indexes as $index )
        {
            if ( $index === $name )
            {
                return TRUE;
            }
        }

        return FALSE;
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
        $cols = $this->prepareIndexName( $columns, $name );

        $query = "CREATE INDEX $name ON $table ( $cols )";

        $result = $this->modify( $query );

        return $result;
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
        $query = "SELECT * FROM sqlite_master WHERE type='index' AND tbl_name = '$table'";

        $results = $this->query( $query );

        $indexes = [ ];

        foreach ( $results as $result )
        {
            $indexes[ ] = $result[ 'name' ];
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
        $query = "DROP INDEX $name";

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
     * @see    http://dev.mysql.com/doc/refman/5.1/en/lock-tables.html
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function lockTables( $tables )
    {
        throw new NotImplementedException();
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
        throw new NotImplementedException();
    }

    /**
     * Copy a table.
     *
     * @param string $table     the table to copy
     * @param string $tempTable temporary table name
     * @param array  $exclude   columns to exclude
     *
     * @throws DatabaseException
     */
    protected function copyTable( $table, $tempTable = '', array $exclude = [ ] )
    {
        $query = "ALTER TABLE $table RENAME TO $tempTable";

        $result = $this->modify( $query );

        if ( FALSE === $result )
        {
            throw new DatabaseException( 'Drop column failed' );
        }

        $columns = $this->getTableColumns( $tempTable, $exclude );

        // create table
        $this->createTable( $table, $columns );

        $this->copyTableData( $table, $tempTable, $columns );
    }

    /**
     * Get a list of table columns.
     *
     * @param string $table
     * @param array  $exclude
     *
     * @return array
     */
    public function getTableColumns( $table, array $exclude = [ ] )
    {
        $query = "pragma table_info( $table )";

        $result = $this->query( $query );

        // grab all columns
        $columns = [ ];

        foreach ( $result as $res )
        {
            foreach ( $res as $key => $value )
            {
                if ( 'name' === $key && !in_array( $value, $exclude ) )
                {
                    $null = ' NULL';

                    if ( TRUE === ( bool ) $res[ 'notnull' ] )
                    {
                        $null = ' NOT NULL';
                    }

                    $columns[ $value ] = $res[ 'type' ] . $null;
                }
            }
        }

        return $columns;
    }

    /**
     * Copy table data from one table to another.
     *
     * @param string $from
     * @param string $to
     * @param array  $columns
     */
    public function copyTableData( $from, $to, $columns )
    {
        // get existing rows
        $cols = implode( ', ', array_keys( $columns ) );
        $cols = rtrim( $cols, ',' );

        // move all data to new table
        $rows = $this->query( "SELECT $cols FROM $to" );

        foreach ( $rows as $row )
        {
            $query = "INSERT INTO $from ( $cols ) VALUES ( " .
                rtrim( str_repeat( '?,', count( $columns ), ',' ) ) .
                " )";

            $values = [ ];

            foreach ( $row as $key => $value )
            {
                if ( in_array( $key, array_keys( $columns ) ) )
                {
                    $values[ ] = $row[ $key ];
                }
            }

            $this->execute( $query, $values );
        }
    }
}