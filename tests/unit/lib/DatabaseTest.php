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
 * @package   RawPHP/RawDatabase/Tests
 * @author    Tom Kaczohca <tom@rawphp.org>
 * @copyright 2014 Tom Kaczocha
 * @license   http://rawphp.org/license.txt MIT
 * @link      http://rawphp.org/
 */

namespace RawPHP\RawDatabase;

/**
 * Database tests.
 * 
 * @category  PHP
 * @package   RawPHP/RawDatabase/Tests
 * @author    Tom Kaczohca <tom@rawphp.org>
 * @copyright 2014 Tom Kaczocha
 * @license   http://rawphp.org/license.txt MIT
 * @link      http://rawphp.org/
 */
class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Database
     */
    public static $db;
    
    /**
     * Tables to cleanup.
     * 
     * @var array
     */
    private $_tables = array();
    
    /**
     * Setup done before suite test run.
     */
    public static function setUpBeforeClass( )
    {
        global $config;
        
        parent::setUpBeforeClass();
        
        self::$db = new Database( );
        self::$db->init( $config );
    }
    
    /**
     * Setup done after suite test run.
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        
        self::$db->mysql->close( );
    }
    
    /**
     * Cleanup after each test.
     */
    protected function tearDown()
    {
        foreach( $this->_tables as $table )
        {
            self::$db->dropTable( $table );
        }
    }
    
    /**
     * Test database is instantiated correctly.
     */
    public function testDatabaseInstanceIsCorrectlyInitialised( )
    {
        $this->assertNotNull( self::$db );
    }
    
    /**
     * Test database query returns correct result.
     */
    public function testQueryReturnsCorrectResult()
    {
        $name = 'test_query_table';
        
        $this->_createTable( $name );
        
        $data1 = 'first';
        
        $query1 = "INSERT INTO $name ( test_name ) VALUES ( '$data1' )";
        
        $this->assertNotFalse( self::$db->insert( $query1 ) );
        
        $query = "SELECT COUNT(*) AS count FROM $name";
        
        $result = self::$db->query( $query );
        $this->assertEquals( 1, $result[ 0 ][ 'count' ] );
    }
    
    /**
     * Test insert returns a valid table record ID.
     */
    public function testInsertReturnsAValidTableRecordId()
    {
        $name = 'test_insert_table';
        $content = 'test content is this';
        
        $this->_createTable( $name );
        
        $text = self::$db->prepareString( $content );
        
        $query = "INSERT INTO $name ( test_name ) VALUES ( ";
        $query .= "'$text'";
        $query .= " )";
        
        $id = self::$db->insert( $query );
        
        $this->assertNotFalse( $id );
        $this->assertTrue( 1 === $id );
    }
    
    /**
     * Test execute.
     */
    public function testExecute()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * Test starting a transaction.
     */
    public function testStartTransaction()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * Test commiting a transaction.
     */
    public function testCommitTransaction()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * Test rolling back a failed transaction.
     */
    public function testRollbackTransaction()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * Test setting transaction auto commit.
     */
    public function testSetTransactionAutoCommit()
    {
        $this->markTestIncomplete();
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
        
        $query1 = "INSERT INTO $name ( test_name ) VALUES ( '$data1' )";
        $query2 = "INSERT INTO $name ( test_name ) VALUES ( '$data2' )";
        
        $this->assertNotFalse( self::$db->insert( $query1 ) );
        $this->assertNotFalse( self::$db->insert( $query2 ) );
        
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
        $name = 'new_add_column_table';
        $content = 'test content here';
        
        $this->_createTable( $name );
        
        $this->assertTrue( self::$db->tableExists( $name ) );
        
        $this->assertTrue( self::$db->addColumn( $name, 'test_col', 'INTEGER(11) NOT NULL' ) );
        
        $text = self::$db->prepareString( $content );
        
        // insert record with new column
        $query = "INSERT INTO $name ( test_name, test_col ) VALUES ( ";
        $query .= "'$text', ";
        $query .= "5";
        $query .= " )";
        
        $id = self::$db->insert( $query );
        
        $this->assertEquals( 1, $id );
    }
    
    /**
     * Test dropping a column.
     */
    public function testDropColumn()
    {
        $name = 'new_drop_column_table';
        $content = 'test content here';
        
        $this->_createTable( $name );
        
        $this->assertTrue( self::$db->tableExists( $name ) );
        
        $text = self::$db->prepareString( $content );
        
        // insert record with new column
        $query = "INSERT INTO $name ( test_name ) VALUES ( ";
        $query .= "'$text', ";
        $query .= " )";
        
        $id = self::$db->insert( $query );
        
        $this->assertFalse( $id );
    }
    
    /**
     * Test index exists returns FALSE when table doesn't exist.
     */
    public function testIndexExistsIsFalse( )
    {
        $table = 'index_exists_table';
        
        $this->_createTable( $table );
        
        $result = self::$db->indexExists( $table, array( 'test_name' ) );
        
        $this->assertFalse( $result );
    }
    
    /**
     * Test index exists returns TRUE when table exists.
     */
    public function testIndexExistsIsTrue( )
    {
        $table = 'index_exists_table';
        
        $this->_createTable( $table );
        
        $result = self::$db->indexExists( $table, array( 'test_name' ) );
        
        $this->assertFalse( $result );
        
        self::$db->addIndex( $table, array( 'test_name' ) );
        
        $result = self::$db->indexExists( $table, array( 'test_name' ) );
        
        $this->assertTrue( $result );
    }
    
    /**
     * Test index exists with multiple columns.
     */
    public function testIndexExistsMultipleColumnsIsTrue( )
    {
        $table = 'index_exists_table';
        
        $this->_createTable( $table );
        
        $result = self::$db->indexExists( $table, array( 'test_name', 'test_value' ) );
        
        $this->assertFalse( $result );
        
        self::$db->addIndex( $table, array( 'test_name', 'test_value' ) );
        
        $result = self::$db->indexExists( $table, array( 'test_name', 'test_value' ) );
        
        $this->assertTrue( $result );
    }
    
    /**
     * Test add index.
     */
    public function testAddIndex( )
    {
        $table = 'add_index_table';
        
        $this->_createTable( $table );
        
        $this->assertTrue( self::$db->addIndex( $table, array( 'test_name' ) ) );
        
        $this->assertTrue( self::$db->indexExists( $table, array( 'test_name' ) ) );
    }
    
    /**
     * Test add index with multiple columns.
     */
    public function testAddIndexMultipleColumns( )
    {
        $table = 'add_index_multiple_col_table';
        
        $this->_createTable( $table );
        
        $this->assertTrue( self::$db->addIndex( $table, array( 'test_name', 'test_value' ) ) );
        
        $this->assertTrue( self::$db->indexExists( $table, array( 'test_name', 'test_value' ) ) );
    }
    
    /**
     * Test get table indexes.
     */
    public function testGetTableIndexes( )
    {
        $table = 'add_index_multiple_col_table';
        
        $this->_createTable( $table );
        
        $this->assertTrue( self::$db->addIndex( $table, array( 'test_name', 'test_value' ) ) );
        
        $indexes = self::$db->getTableIndexes( $table );
        
        $this->assertEquals( 2, count( $indexes ) );
        $this->assertEquals( 'test_id', $indexes[ 0 ][ 'PRIMARY' ][ 0 ] );
        $this->assertEquals( 'test_name', $indexes[ 1 ][ 'test_name' ][ 0 ] );
        $this->assertEquals( 'test_value', $indexes[ 1 ][ 'test_name' ][ 1 ] );
    }
    
    /**
     * Test drop index.
     */
    public function testDropIndex()
    {
        $table = 'drop_index_table';
        
        $this->_createTable( $table );
        
        $this->assertTrue( self::$db->addIndex( $table, array( 'test_name', 'test_value' ) ) );
        
        $indexes = self::$db->getTableIndexes( $table );
        
        $keys = array_keys( $indexes[ 1 ] );
        
        $this->assertEquals( 'test_name', $keys[ 0 ] );
        
        $this->assertTrue( self::$db->dropIndex( $table, $keys[ 0 ] ) );
        
        $this->assertFalse( self::$db->indexExists( $table, array( 'test_name', 'test_value' ) ) );
    }
    
    /**
     * Test add foreign key to table.
     */
    public function testAddForeignKey()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * Test dropping a foreign key.
     */
    public function testDropForeignKey()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * Test getting all foreign keys for a table.
     */
    public function testGetTableForeignKeys()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * Test preparing string.
     */
    public function testPrepareString()
    {
        $original = "This'll do. Don't you think?";
        $expecting = "This\'ll do. Don\'t you think?";
        
        $result = self::$db->prepareString( $original );
        
        $this->assertEquals( $expecting, $result );
    }
    
    /**
     * Helper method to that creates a test table.
     * 
     * @param string $name table name
     * @param bool   $drop whether to drop table when done
     */
    private function _createTable( $name, $drop = TRUE )
    {
        $columns = array(
            'test_id' => 'INTEGER(11) PRIMARY KEY AUTO_INCREMENT NOT NULL',
            'test_name' => 'VARCHAR(64) NOT NULL',
            'test_value' => 'VARCHAR(128) NULL',
        );
        
        self::$db->createTable( $name, $columns );
        
        if ( $drop )
        {
            $this->_tables[] = $name;
        }
    }
}