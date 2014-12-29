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

use PDO;
use RawPHP\RawDatabase\Contract\IDatabase;
use RawPHP\RawDatabase\Exception\DatabaseException;

/**
 * Class Handler
 *
 * @package RawPHP\RawDatabase\Handler
 */
abstract class Handler implements IDatabase
{
    /** @var  string */
    protected $host;
    /** @var  string */
    protected $user;
    /** @var  string */
    protected $password;
    /** @var  string */
    protected $name;

    /** @var  string */
    protected $query;

    /** @var  PDO */
    protected $database;

    /**
     * Close the database connection.
     *
     * @throws DatabaseException
     */
    public function close()
    {
        $this->database = NULL;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Call this function when you're expecting a result
     * from queries like SELECT.
     *
     * @param string $query the query string
     * @param array  $data
     *
     * @return mixed list of results or FALSE
     *
     * @throws DatabaseException
     */
    public function query( $query, array $data = [ ] )
    {
        $statement = $this->database->prepare( $query );

        if ( FALSE === $statement )
        {
            throw new DatabaseException( 'Failed to create prepared statement.' );
        }

        $result = $statement->execute( $data );

        $this->query = $statement->queryString;

        if ( FALSE === $result )
        {
            return [ ];
        }

        return $statement->fetchAll();
    }

    /**
     * Inserts a record into the database.
     *
     * @param string $query the query string
     * @param array  $data
     *
     * @return mixed inserted ID on success, FALSE on failure
     *
     * @throws DatabaseException
     */
    public function insert( $query, array $data = [ ] )
    {
        $id = NULL;

        $statement = $this->database->prepare( $query );

        if ( FALSE === $statement )
        {
            $this->close();

            throw new DatabaseException( 'Failed to create prepared statement.' );
        }

        $result = $statement->execute( $data );

        $this->query = $statement->queryString;

        if ( FALSE !== $result )
        {
            $result = ( int ) $this->database->lastInsertId();
        }

        return $result;
    }

    /**
     * Executes a database query which does not return a value.
     *
     * @param string $query the query
     * @param array  $data
     *
     * @return bool|int number of affected rows on success, FALSE on failure
     *
     * @throws DatabaseException
     */
    public function execute( $query, array $data = [ ] )
    {
        $result = NULL;

        $statement = $this->database->prepare( $query );

        if ( FALSE === $statement )
        {
            $this->close();

            throw new DatabaseException( 'Failed to create prepared statement.' );
        }

        $result = $statement->execute( $data );

        $this->query = $statement->queryString;

        if ( FALSE !== $result )
        {
            $result = ( int ) $statement->rowCount();
        }

        return $result;
    }

    /**
     * Executes database modification query.
     *
     * @param string $query the query
     * @param array  $data
     *
     * @return bool TRUE on success, FALSE on failure
     *
     * @throws DatabaseException
     */
    public function modify( $query, array $data = [ ] )
    {
        $statement = $this->database->prepare( $query );

        if ( FALSE === $statement )
        {
            throw new DatabaseException( 'Failed to create prepared statement.' );
        }

        $result = $statement->execute( $data );

        $this->query = $statement->queryString;

        return ( FALSE !== $result );
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
        $this->database->beginTransaction();
    }

    /**
     * Commits a database transaction.
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function commitTransaction()
    {
        $this->database->commit();
    }

    /**
     * Reverses a database transaction.
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public function rollbackTransaction()
    {
        $this->database->rollBack();
    }

    /**
     * Sets the AUTO COMMIT option.
     *
     * @param bool $autoCommit whether auto commit should be on
     *
     * @return bool TRUE on success, FALSE on failure
     */
    public abstract function setTransactionAutoCommit( $autoCommit = FALSE );

    /**
     * Prepare columns and index name.
     *
     * @param array  $columns
     * @param string $name
     *
     * @return string
     */
    protected function prepareIndexName( array $columns, &$name )
    {
        $cols = implode( ', ', $columns );
        $cols = rtrim( $cols, ', ' );

        if ( '' === $name )
        {
            $name = 'index_' . str_replace( ', ', '_', $cols );
        }

        return $cols;
    }
}
