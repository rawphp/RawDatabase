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
use PDO;
use RawPHP\RawDatabase\Exception\DatabaseException;

/**
 * The database class provides MySQL database services.
 *
 * @category  PHP
 * @package   RawPHP\RawDatabase
 * @author    Tom Kaczocha <tom@rawphp.org>
 * @copyright 2014 Tom Kaczocha
 * @license   http://rawphp.org/license.txt MIT
 * @link      http://rawphp.org/
 */
class MySql extends Database
{
    /**
     * Initialises the database and creates a new
     * connection.
     *
     * @param array $config configuration array
     *
     * @throws DatabaseException
     */
    public function connect( $config = NULL )
    {
        if ( $config !== NULL )
        {
            $this->host     = $config[ 'db_host' ];
            $this->user     = $config[ 'db_user' ];
            $this->password = $config[ 'db_pass' ];
            $this->name     = $config[ 'db_name' ];

            try
            {
                $dns = "mysql:host=" . $this->host . ";dbname=" . $this->name;

                $this->database = new PDO( $dns, $config[ 'db_user' ], $config[ 'db_pass' ] );
            }
            catch ( Exception $e )
            {
                throw new DatabaseException( $e->getMessage() );
            }
        }
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
        $this->query = "START TRANSACTION;";

        return $this->modify( $this->query );
    }

    /**
     * Commits a database transaction.
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function commitTransaction()
    {
        $this->query = "COMMIT;";

        return $this->modify( $this->query );
    }

    /**
     * Reverses a database transaction.
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function rollbackTransaction()
    {
        $this->query = "ROLLBACK;";

        return $this->modify( $this->query );
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
        $this->query = "SET autocommit = 0;";

        $result = NULL;

        if ( !$autoCommit )
        {
            $result = $this->modify( $this->query );
        }

        return $result;
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
        $query = "SELECT COUNT(*) AS count FROM information_schema.tables
                  WHERE table_schema = ? AND table_name = ?";

        $params = [ $this->name, $table ];

        $statement = $this->database->prepare( $query );
        $statement->execute( $params );

        $results = $statement->fetchAll();

        return ( 0 < ( int ) $results[ 0 ][ 'count' ] );
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
        $this->query = "TRUNCATE TABLE $table;";

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
    public function createTable( $name, $columns = [ ], $charSet = 'utf8',
                                 $engine = 'InnoDB', $collation = 'utf8_unicode_ci'
    )
    {
        $query = "CREATE TABLE IF NOT EXISTS $name ( ";

        foreach ( $columns as $name => $type )
        {
            $query .= "$name $type, ";
        }

        $query = rtrim( $query, ', ' );

        $query .= " ) ";
        $query .= "ENGINE=$engine ";
        $query .= "DEFAULT CHARSET $charSet ";
        $query .= "COLLATE $collation;";

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
        $this->query = "DROP TABLE IF EXISTS $table;";

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
        $query = "ALTER TABLE $table ADD ";
        $query .= "$name $type;";

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
        $query = "ALTER TABLE $table DROP COLUMN $name;";

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
     * <pre>
     * $key = array(
     *     'key_name'    => 'fk_self_table,
     *     'self_column' => 'order_id',
     *     'ref_table    => 'orders',
     *     'ref_column'  => 'order_id',
     *     'on_delete'   => SET NULL',
     *     'on_update'   => SET NULL',
     * );
     * </pre>
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function addForeignKey( $table, $key = [ ] )
    {
        $keyName  = $key[ 'key_name' ];
        $selfCol  = $key[ 'self_column' ];
        $refTable = $key[ 'ref_table' ];
        $refCol   = $key[ 'ref_column' ];

        $query = "ALTER TABLE $table ";
        $query .= "ADD CONSTRAINT $keyName FOREIGN KEY ";
        $query .= "( $selfCol ) REFERENCES $refTable ( $refCol )";

        if ( isset( $key[ 'on_delete' ] ) )
        {
            $delete = $key[ 'on_delete' ];

            $query .= " ON DELETE $delete";
        }

        if ( isset( $key[ 'on_update' ] ) )
        {
            $update = $key[ 'on_update' ];

            $query .= " ON UPDATE $update";
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
        $query = "ALTER TABLE $table DROP FOREIGN KEY $keyName";

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
                  FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA = ?
                  AND TABLE_NAME = ? AND REFERENCED_COLUMN_NAME IS NOT NULL;';

        $params = [ $this->name, $table ];

        $statement = $this->database->prepare( $query );
        $statement->execute( $params );

        $results = $statement->fetchAll();

        $keys = [ ];

        if ( !empty( $results ) )
        {
            foreach ( $results as $result )
            {
                $record                          = [ ];
                $record[ 'database' ]            = $result[ 'CONSTRAINT_SCHEMA' ];
                $record[ 'key_name' ]            = $result[ 'CONSTRAINT_NAME' ];
                $record[ 'table' ]               = $result[ 'TABLE_NAME' ];
                $record[ 'column' ]              = $result[ 'COLUMN_NAME' ];
                $record[ 'referenced_database' ] = $result[ 'REFERENCED_TABLE_SCHEMA' ];
                $record[ 'referenced_table' ]    = $result[ 'REFERENCED_TABLE_NAME' ];
                $record[ 'referenced_column' ]   = $result[ 'REFERENCED_COLUMN_NAME' ];

                $keys[ ] = $record;
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
     *
     * @throws Exception
     */
    public function indexExists( $table, $columns = [ ] )
    {
        if ( !is_array( $columns ) )
        {
            throw new Exception( 'columns must be an array' );
        }

        $query = "SELECT * FROM information_schema.statistics WHERE table_schema =
                 ? AND table_name = ? AND ";

        $params = [ $this->name, $table ];

        foreach ( $columns as $column )
        {
            $query .= "column_name = ? OR ";
            $params[ ] = &$column;
        }

        $query = rtrim( $query, ' OR ' );

        $statement = $this->database->prepare( $query );
        $statement->execute( $params );

        $result = $statement->fetchAll();

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

        $cols = $this->prepareIndexName( $columns, $name );

        $query = "ALTER TABLE $table ADD $type $name ( $cols )";

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
                 ? AND table_name = ?";

        $params = [ $this->name, $table ];

        $statement = $this->database->prepare( $query );
        $statement->execute( $params );

        $results = $statement->fetchAll();

        $indexes = [ ];

        if ( !empty( $results ) )
        {
            $name = NULL;

            $i = 0;

            foreach ( $results as $result )
            {
                if ( !in_array( $result[ 'INDEX_NAME' ], $indexes ) )
                {
                    $indexes[ ] = $result[ 'INDEX_NAME' ];
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
        $query = "ALTER TABLE $table DROP INDEX $name";

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
        if ( is_array( $tables ) )
        {
            $tmp = '';

            foreach ( $tables as $table )
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
     * @see    http://dev.mysql.com/doc/refman/5.1/en/lock-tables.html
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function unlockTables()
    {
        $query = "UNLOCK TABLES;";

        return $this->modify( $query );
    }
}