<?php

/**
 * This file is part of Step in Deals application.
 *
 * Copyright (c) 2014 Tom Kaczocha
 *
 * This Source Code is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, you can obtain one at http://mozilla.org/MPL/2.0/.
 *
 * PHP version 5.4
 *
 * @category  PHP
 * @package   RawPHP\RawDatabase\Tests
 * @author    Tom Kaczohca <tom@crazydev.org>
 * @copyright 2014 Tom Kaczocha
 * @license   http://crazydev.org/licenses/mpl.txt MPL
 * @link      http://crazydev.org/
 */

namespace RawPHP\RawDatabas\Tests;

use Exception;
use PHPUnit_Framework_TestCase;
use RawPHP\RawDatabase\Contract\IDatabase;
use RawPHP\RawDatabase\Database;

/**
 * Class SQLiteTest
 *
 * @package RawPHP\RawDatabase
 */
class SQLiteTest extends PHPUnit_Framework_TestCase
{
    /** @var  Database */
    protected static $db;

    protected $dbName = '';

    /**
     * Setup before each test.
     */
    protected function setUp()
    {
        global $config;

        parent::setUp();

        $this->dbName = OUTPUT_DIR . $config[ 'db_name' ] . '.sqlite';

        $config[ 'db_name' ] = $this->dbName;
        $config[ 'handler' ] = 'sqlite';

        self::$db = new Database( $config );
    }

    /**
     * Cleanup after each test.
     */
    protected function tearDown()
    {
        parent::tearDown();

        if ( NULL !== self::$db )
        {
            self::$db->close();
        }

        if ( file_exists( $this->dbName ) )
        {
            unlink( $this->dbName );
        }
    }

    /**
     * Test database is instantiated correctly.
     */
    public function testDatabaseInstanceIsCorrectlyInitialised()
    {
        $this->assertNotNull( self::$db );
    }

    /**
     * Test failed connection throws exception.
     *
     * @expectedException \RawPHP\RawDatabase\Exception\DatabaseException
     */
    public function testFailedConnectionThrowsException()
    {
        if ( NULL !== self::$db )
        {
            self::$db->close();
        }

        new Database( [ 'handler' => 'sqlite', 'db_name' => NULL ] );
    }

    /**
     * Test database query returns correct result.
     */
    public function testQueryReturnsCorrectResult()
    {
        $name = 'test_query_table';

        $this->_createTable( $name );

        $data1 = 'first';

        $query1 = "INSERT INTO $name ( test_name ) VALUES ( ? )";

        $id = self::$db->insert( $query1, [ $data1 ] );

        $this->assertNotFalse( $id );
        $this->assertEquals( 1, $id );

        $id = self::$db->insert( $query1, [ $data1 ] );

        $this->assertEquals( 2, $id );

        $query = "SELECT COUNT(*) AS count FROM $name";

        $result = self::$db->query( $query );

        $this->assertEquals( 2, $result[ 0 ][ 'count' ] );
    }

    /**
     * Test insert returns a valid table record ID.
     */
    public function testInsertReturnsAValidTableRecordId()
    {
        $name    = 'test_insert_table';
        $content = 'test content is this';

        $this->_createTable( $name );

        $query = "INSERT INTO $name ( test_name ) VALUES ( ";
        $query .= "?";
        $query .= " )";

        $id = self::$db->insert( $query, [ $content ] );

        $this->assertNotFalse( $id );
        $this->assertTrue( 1 === $id );
    }

    /**
     * Test execute.
     */
    public function testExecute()
    {
        $table = 'test_table';

        $this->_createTable( $table );

        $query = "INSERT INTO $table ( test_name, test_value ) VALUES
            ( ?, ? )";

        $result = self::$db->execute( $query, [ 'name1', 'value1' ] );

        $this->assertEquals( 1, $result );
    }

    /**
     * Test committing a transaction.
     */
    public function testCommitTransaction()
    {
        $table = 'test_table';

        $this->_createTable( $table );

        self::$db->startTransaction();

        $query = "INSERT INTO $table ( test_name, test_value ) VALUES ( ?, ? )";

        $this->assertEquals( 1, self::$db->execute( $query, [ 'name1', 'value1' ] ) );
        $this->assertEquals( 1, self::$db->execute( $query, [ 'name2', 'value2' ] ) );

        self::$db->commitTransaction();

        $query = "SELECT * FROM $table";

        $results = self::$db->query( $query );

        $this->assertEquals( 2, count( $results ) );
    }

    /**
     * Test rolling back a failed transaction.
     */
    public function testRollbackTransaction()
    {
        $table = 'test_table';

        $this->_createTable( $table );

        self::$db->startTransaction();

        $query = "INSERT INTO $table ( test_name, test_value ) VALUES ( ?, ? )";

        $this->assertEquals( 1, self::$db->execute( $query, [ 'name1', 'value1' ] ) );
        $this->assertEquals( 1, self::$db->execute( $query, [ 'name2', 'value2' ] ) );

        self::$db->rollbackTransaction();

        $query = "SELECT * FROM $table";

        $results = self::$db->query( $query );

        $this->assertEquals( 0, count( $results ) );
    }

    /**
     * Test table exists returns TRUE when table exists.
     */
    public function testTableExistsReturnsTrueWhenTableExists()
    {
        $name = 'not_exist_table';

        $this->assertFalse( self::$db->tableExists( $name ) );

        $name = 'test_table_exists_table';

        $this->_createTable( $name );

        $this->assertTrue( self::$db->tableExists( $name ) );
    }

    /**
     * Test truncate table.
     */
    public function testTruncateTable()
    {
        $name = 'test_truncate_table';

        $this->_createTable( $name );

        $data1 = 'first';
        $data2 = 'second';

        $query1 = "INSERT INTO $name ( test_name ) VALUES ( ? )";

        $this->assertNotFalse( self::$db->insert( $query1, [ $data1 ] ) );
        $this->assertNotFalse( self::$db->insert( $query1, [ $data2 ] ) );

        $query = "SELECT COUNT(*) AS count FROM $name";

        $result = self::$db->query( $query );
        $this->assertEquals( 2, $result[ 0 ][ 'count' ] );

        self::$db->truncateTable( $name );

        $result = self::$db->query( $query );
        $this->assertEquals( 0, $result[ 0 ][ 'count' ] );
    }

    /**
     * Test create table.
     */
    public function testCreateTable()
    {
        $name = 'test_create_table';

        $this->_createTable( $name );

        $this->assertTrue( self::$db->tableExists( $name ) );
    }

    /**
     * Test dropping a table.
     */
    public function testDropTable()
    {
        $name = 'test_drop_table';

        $this->_createTable( $name, FALSE );

        $this->assertTrue( self::$db->tableExists( $name ) );

        self::$db->dropTable( $name );

        $this->assertFalse( self::$db->tableExists( $name ) );
    }

    /**
     * Test adding a column.
     */
    public function testAddColumn()
    {
        $name    = 'new_add_column_table';
        $content = 'test content here';

        $this->_createTable( $name );

        $this->assertTrue( self::$db->tableExists( $name ) );

        $this->assertTrue( self::$db->addColumn( $name, 'test_col', 'INTEGER' ) );

        // insert record with new column
        $query = "INSERT INTO $name ( test_name, test_col ) VALUES ( ?, ? )";

        $id = self::$db->insert( $query, [ $content, 5 ] );

        $this->assertEquals( 1, $id );
    }

    /**
     * Test dropping a column.
     */
    public function testDropColumn()
    {
        $name    = 'new_drop_column_table';
        $content = 'test content here';

        $this->_createTable( $name );

        $this->assertTrue( self::$db->tableExists( $name ) );

        $query = "INSERT INTO $name ( test_name, test_value ) VALUES ( ?, ? );";

        $this->assertNotFalse( self::$db->insert( $query, [ 'initial name', 'initial content' ] ) );

        self::$db->dropColumn( $name, 'test_name' );

        $this->assertCount( 1, self::$db->query( "SELECT * FROM $name" ) );

        // insert record with new column
        $query = "INSERT INTO $name ( test_name ) VALUES ( ? );";

        try
        {
            $result = self::$db->insert( $query, [ $content ] );

            $this->assertFalse( $result );
        }
        catch ( Exception $e )
        {
        }
    }

    /**
     * Test index exists returns FALSE when table doesn't exist.
     */
    public function testIndexExistsIsFalse()
    {
        $table = 'index_exists_table';

        $this->_createTable( $table );

        $result = self::$db->indexExists( $table, [ 'test_name' ] );

        $this->assertFalse( $result );
    }

    /**
     * Test index exists returns TRUE when table exists.
     */
    public function testIndexExistsIsTrue()
    {
        $table = 'index_exists_table';

        $this->_createTable( $table );

        $result = self::$db->indexExists( $table, [ 'test_name' ] );

        $this->assertFalse( $result );

        self::$db->addIndex( $table, [ 'test_name' ] );

        $result = self::$db->indexExists( $table, [ 'test_name' ] );

        $this->assertTrue( $result );
    }

    /**
     * Test index exists with multiple columns.
     */
    public function testIndexExistsMultipleColumnsIsTrue()
    {
        $table = 'index_exists_table';

        $this->_createTable( $table );

        $result = self::$db->indexExists( $table, [ 'test_name', 'test_value' ] );

        $this->assertFalse( $result );

        self::$db->addIndex( $table, [ 'test_name', 'test_value' ] );

        $result = self::$db->indexExists( $table, [ 'test_name', 'test_value' ] );

        $this->assertTrue( $result );
    }

    /**
     * Test add index.
     */
    public function testAddIndex()
    {
        $table = 'add_index_table';

        $this->_createTable( $table );

        $this->assertTrue( self::$db->addIndex( $table, [ 'test_name' ] ) );

        $this->assertTrue( self::$db->indexExists( $table, [ 'test_name' ] ) );
    }

    /**
     * Test add index with multiple columns.
     */
    public function testAddIndexMultipleColumns()
    {
        $table = 'add_index_multiple_col_table';

        $this->_createTable( $table );

        $this->assertTrue( self::$db->addIndex( $table, [ 'test_name', 'test_value' ] ) );

        $this->assertTrue( self::$db->indexExists( $table, [ 'test_name', 'test_value' ] ) );
    }

    /**
     * Test get table indexes.
     */
    public function testGetTableIndexes()
    {
        $table = 'add_index_multiple_col_table';

        $this->_createTable( $table );

        $this->assertTrue( self::$db->addIndex( $table, [ 'test_name', 'test_value' ] ) );

        $indexes = self::$db->getTableIndexes( $table );

        $this->assertEquals( 1, count( $indexes ) );
    }

    /**
     * Test drop index.
     */
    public function testDropIndex()
    {
        $table = 'drop_index_table';

        $this->_createTable( $table );

        $this->assertTrue( self::$db->addIndex( $table, [ 'test_name', 'test_value' ] ) );

        $indexes = self::$db->getTableIndexes( $table );

        $this->assertEquals( 'index_test_name_test_value', $indexes[ 0 ] );

        $this->assertTrue( self::$db->dropIndex( $table, 'index_test_name_test_value' ) );

        $this->assertFalse( self::$db->indexExists( $table, [ 'test_name', 'test_value' ] ) );
    }

    /**
     * Test add foreign key to table.
     */
    public function testAddForeignKey()
    {
        $table1 = 'key_table1';
        $table2 = 'key_table2';

        $this->assertTrue( $this->_createTable( $table1 ) );
        $this->assertTrue( $this->_createTable( $table2 ) );

        self::$db->addIndex( $table1, [ 'test_name' ], 'my_index1', IDatabase::INDEX_INDEX );
        self::$db->addIndex( $table2, [ 'test_name' ], 'my_index2', IDatabase::INDEX_INDEX );

        $this->assertTrue(
            self::$db->addForeignKey(
                $table1,
                [
                    'key_name'    => 'key_table_key1',
                    'self_column' => 'test_name',
                    'ref_table'   => $table2,
                    'ref_column'  => 'test_name',
                ]
            )
        );
    }

//    /**
//     * Test dropping a foreign key.
//     */
//    public function testDropForeignKey()
//    {
//        $table1 = 'key_table1';
//        $table2 = 'key_table2';
//
//        $this->assertTrue( $this->_createTable( $table1 ) );
//        $this->assertTrue( $this->_createTable( $table2 ) );
//
//        self::$db->addIndex( $table1, [ 'test_name' ], 'my_index1', IDatabase::INDEX_INDEX );
//        self::$db->addIndex( $table2, [ 'test_name' ], 'my_index2', IDatabase::INDEX_INDEX );
//
//        $this->assertTrue(
//            self::$db->addForeignKey(
//                $table1,
//                [
//                    'key_name'    => 'key_table_key',
//                    'self_column' => 'test_name',
//                    'ref_table'   => $table2,
//                    'ref_column'  => 'test_name',
//                ]
//            )
//        );
//
//        $keys = self::$db->getTableForeignKeys( $table1 );
//
//        $this->assertEquals( 'key_table_key', $keys[ 0 ][ 'key_name' ] );
//
//        self::$db->dropForeignKey( $table1, 'key_table_key' );
//
//        $this->assertCount( 0, self::$db->getTableForeignKeys( $table1 ) );
//    }

//    /**
//     * Test getting all foreign keys for a table.
//     */
//    public function testGetTableForeignKeys()
//    {
//        $table1 = 'key_table1';
//        $table2 = 'key_table2';
//
//        $this->assertTrue( $this->_createTable( $table1 ) );
//        $this->assertTrue( $this->_createTable( $table2 ) );
//
//        self::$db->addIndex( $table1, [ 'test_name' ], 'my_index1', IDatabase::INDEX_INDEX );
//        self::$db->addIndex( $table1, [ 'test_value' ], 'my_index2', IDatabase::INDEX_INDEX );
//        self::$db->addIndex( $table2, [ 'test_name' ], 'my_index3', IDatabase::INDEX_INDEX );
//        self::$db->addIndex( $table2, [ 'test_value' ], 'my_index4', IDatabase::INDEX_INDEX );
//
//        $this->assertTrue(
//            self::$db->addForeignKey(
//                $table1,
//                [
//                    'key_name'    => 'key_table_key1',
//                    'self_column' => 'test_name',
//                    'ref_table'   => $table2,
//                    'ref_column'  => 'test_name',
//                ]
//            )
//        );
//
//        $this->assertTrue(
//            self::$db->addForeignKey(
//                $table1,
//                [
//                    'key_name'    => 'key_table_key2',
//                    'self_column' => 'test_value',
//                    'ref_table'   => $table2,
//                    'ref_column'  => 'test_value',
//                    'on_delete'   => 'SET NULL',
//                    'on_update'   => 'SET NULL',
//                ]
//            )
//        );
//
//        $keys = self::$db->getTableForeignKeys( $table1 );
//
//        $this->assertNotNull( $keys );
//        $this->assertEquals( 2, count( $keys ) );
//
//        $this->assertEquals( 'key_table_key',
//                             substr( $keys[ 0 ][ 'key_name' ], 0, strlen( $keys[ 0 ][ 'key_name' ] ) - 1 )
//        );
//        $this->assertEquals( 'key_table_key',
//                             substr( $keys[ 1 ][ 'key_name' ], 0, strlen( $keys[ 1 ][ 'key_name' ] ) - 1 )
//        );
//    }

    /**
     * Helper method to that creates a test table.
     *
     * @param string $name table name
     * @param bool   $drop whether to drop table when done
     *
     * @return bool TRUE on success, FALSE on failure
     */
    private function _createTable( $name, $drop = TRUE )
    {
        $columns = [
            'test_id'    => 'INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL',
            'test_name'  => 'TEXT NOT NULL',
            'test_value' => 'TEXT NULL',
        ];

        return self::$db->createTable( $name, $columns );
    }
}