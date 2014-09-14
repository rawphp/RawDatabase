# RawDatabase - A Simple MySQLi Database Wrapper for PHP Applications

## Package Features
- Query your database with `query( )`
- Insert records with `insert( )`
- Execute commands with `execute( )`
- Create and drop tables with `createTable( )` and `dropTable( )`
- Add and drop columns with `addColumn( )` and `dropColumn( )`
- Add and drop indexes with `addIndex( )` and `dropIndex( )`
- Supports INDEX, PRIMARY KEY, UNIQUE, FULLTEXT and SPATIAL index
- Add and drop foreign keys with `addForeignKey( )` and `dropForeignKey( )`
- Transaction support with `startTransaction( )`, `commitTransaction( )` and `rollbackTransaction( )`
- Lock and unlock tables with `lockTables( )` and `unlockTables( )`

## Installation

### Composer
RawDatabase is available via [Composer/Packagist](https://packagist.org/packages/rawphp/raw-database).

Add `"rawphp/raw-database": "0.1.1"` to the require block in your composer.json and then run `composer install`.

```json
{
        "require": {
            "rawphp/raw-database": "0.1.1"
        }
}
```

You can also simply run the following from the command line:

```sh
composer require rawphp/raw-database "0.1.1"
```

### Tarball
Alternatively, just copy the contents of the RawDatabase folder into somewhere that's in your PHP `include_path` setting. If you don't speak git or just want a tarball, click the 'zip' button at the top of the page in GitHub.

## Basic Usage

```php
<?php

use RawPHP\RawDatabase\Database;

// configuration
$config = array(
    'db_name'   => 'database_name',
    'db_user'   => 'user',
    'db_pass'   => 'password',
    'db_host'   => 'localhost',
);

// create a new instance of database
$db = new Database( );
$db->init( $config );

// query the users table - returns an array of key->value pairs
$results = $db->query( "SELECT * FROM users" );

// insert a record

// escape strings before inserting into the database
$username = $db->prepareString( $user->username );

// insert returns the new record ID
$id = $db->insert( "INSERT INTO users ( user_name ) VALUES ( '$username' )" );

// add table
$result = $db->createTable( 'users', array( 
                'user_id'   => 'INTEGER(11) PRIMARY KEY AUTO_INCREMENT NOT NULL',
                'username   => 'VARCHAR(32) NOT NULL,
            )
);

// drop table
$result = $db->dropTable( 'users' );

// add index
$result = $db->addIndex( 'table_name', array( 'column1', 'column2' ), 'index_name', $index_type );

// drop index
$result = $db->dropIndex( 'table_name', 'index_name' );

```

## License
This package is licensed under the [MIT](https://github.com/rawphp/RawDatabase/blob/master/LICENSE). Read LICENSE for information on the software availability and distribution.

## Contributing

Please submit bug reports, suggestions and pull requests to the [GitHub issue tracker](https://github.com/rawphp/RawDatabase/issues).

## Changelog

#### 14-09-2014
- Implemented the hook system.

#### 13-09-2014
- Moved database initialisation into `init()`

#### 11-09-2014
- Initial Code Commit
