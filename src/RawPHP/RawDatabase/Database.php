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

use PDO;
use RawPHP\RawDatabase\Contract\IDatabase;
use RawPHP\RawDatabase\Exception\DatabaseException;

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
abstract class Database implements IDatabase
{
    /** @var  string */
    protected $host;
    /** @var  string */
    protected $user;
    /** @var  string */
    protected $password;
    /** @var  string */
    protected $name;

    /** @var  PDO */
    protected $database;
    /** @var  string */
    protected $query;

    /**
     * Create a new database instance.
     *
     * @param array $config
     */
    public function __construct( array $config = [ ] )
    {
        $this->connect( $config );
    }

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
     * Get last executed query.
     *
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
     */
    public function query( $query, array $data = [ ] )
    {
        $statement = $this->database->prepare( $query );
        $result    = $statement->execute( $data );

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
     */
    public function insert( $query, array $data = [ ] )
    {
        $id = NULL;

        $statement = $this->database->prepare( $query );
        $result    = $statement->execute( $data );

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
     */
    public function execute( $query, array $data = [ ] )
    {
        $result = NULL;

        $statement = $this->database->prepare( $query );
        $result    = $statement->execute( $data );

        $this->query = $statement->queryString;

        if ( FALSE !== $result )
        {
            $result = ( int )$statement->rowCount();
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
     */
    public function modify( $query, array $data = [ ] )
    {
        $statement = $this->database->prepare( $query );
        $result    = $statement->execute( $data );

        $this->query = $statement->queryString;

        return ( FALSE !== $result );
    }

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