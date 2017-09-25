# GithubRepoLister

Gets Github repo list and logs each search.

## Requirements

 * PHP 7.0+
   * PDO (PHP Data Objects) extension (`pdo`)

## Installation

 1. Include the library via Composer :

    ```
    $ composer create-project janrydrych/github-repo-lister .
    ```

 1. Configuration directives can be changed in the [Configuration](src/Configuration.php) class. Feel free to modify it according to your needs and desired database platform.
  
 1. The project uses [delight-im/PHP-Auth](https://github.com/delight-im/PHP-Auth), so the database for the authentication has to be created. The DSN and credentials are set in [Configuration](src/Configuration.php) and even the database schema is available for [SQLite](https://github.com/delight-im/PHP-Auth/blob/master/Database/SQLite.sql) and [MySQL](https://github.com/delight-im/PHP-Auth/blob/master/Database/MySQL.sql) as well, so basically create DB on your favourite platform and import the schema.
    
 1. New users for the delight-im/PHP-Auth can be created according to its [documentation](https://github.com/delight-im/PHP-Auth#creating-new-users) or the project contains small utility file [user-creator.php](web/user-creator.php) for such a purpose (edit the file, insert desired username credentials and run it).
 
 1. Remove the [user-creator.php](web/user-creator.php).
     
 1. Data storage database is initialized automatically, even if you delete it.

## Usage
 There are three main files:
 1. [repo-list.php](web/repo-list.php) which shows the list of public repositories for user.
 
 1. [search-log.php](web/search-log.php) which shows paginated list of api queries that has been made.
 
 1. [log-purge.php](web/log-purge.php) which is authenticated (see Installation 3. and 4.) and deletes logs that are older than entered hours. 
