<?php

declare(strict_types=1);

namespace Tests\Helpers\Constants;

use LionDatabase\Helpers\Constants\MySQLConstantsTrait;
use LionTest\Test;

class MySQLConstantsTraitTest extends Test
{
    use MySQLConstantsTrait;

    const MYSQL_KEYWORDS_VALUES = [
        'charset' => ' CHARSET',
        'status' => ' STATUS',
        'replace' => ' REPLACE',
        'end' => ' END',
        'begin' => ' BEGIN',
        'exists' => ' EXISTS',
        'if' => ' IF',
        'procedure' => ' PROCEDURE',
        'use' => ' USE',
        'engine' => ' ENGINE',
        'collate' => ' COLLATE',
        'character' => ' CHARACTER',
        'schema' => ' SCHEMA',
        'database' => ' DATABASE',
        'full' => ' FULL',
        'with' => ' WITH',
        'recursive' => ' RECURSIVE',
        'year' => ' YEAR(?)',
        'month' => ' MONTH(?)',
        'day' => ' DAY(?)',
        'in' => ' IN(?)',
        'where' => ' WHERE',
        'as' => ' AS',
        'and' => ' AND',
        'or' => ' OR',
        'between' => ' BETWEEN',
        'select' => ' SELECT',
        'from' => ' FROM',
        'join' => ' JOIN',
        'on' => ' ON',
        'left' => ' LEFT',
        'right' => ' RIGHT',
        'inner' => ' INNER',
        'insert' => ' INSERT',
        'into' => ' INTO',
        'values' => ' VALUES',
        'update' => ' UPDATE',
        'set' => ' SET',
        'delete' => ' DELETE',
        'call' => ' CALL',
        'like' => ' LIKE',
        'group-by' => ' GROUP BY',
        'asc' => ' ASC',
        'desc' => ' DESC',
        'order-by' => ' ORDER BY',
        'count' => ' COUNT(?)',
        'max' => ' MAX(?)',
        'min' => ' MIN(?)',
        'sum' => ' SUM(?)',
        'avg' => ' AVG(?)',
        'limit' => ' LIMIT',
        'having' => ' HAVING',
        'show' => ' SHOW',
        'tables' => ' TABLES',
        'columns' => ' COLUMNS',
        'drop' => ' DROP',
        'table' => ' TABLE',
        'index' => ' INDEX',
        'unique' => ' UNIQUE',
        'create' => ' CREATE',
        'view' => ' VIEW',
        'concat' => ' CONCAT(*)',
        'union' => ' UNION',
        'all' => ' ALL',
        'distinct' => ' DISTINCT',
        'offset' => ' OFFSET',
        'primary-key' => ' PRIMARY KEY (?)',
        'auto-increment' => ' AUTO_INCREMENT',
        'comment' => ' COMMENT',
        'default' => ' DEFAULT',
        'is-not-null' => ' IS NOT NULL',
        'is-null' => ' IS NULL',
        'null' => ' NULL',
        'not-null' => ' NOT NULL',
        'int' => ' INT(?)',
        'bigint' => ' BIGINT(?)',
        'decimal' => ' DECIMAL',
        'double' => ' DOUBLE',
        'float' => ' FLOAT',
        'mediumint' => ' MEDIUMINT(?)',
        'real' => ' REAL',
        'smallint' => ' SMALLINT(?)',
        'tinyint' => ' TINYINT(?)',
        'blob' => ' BLOB',
        'varbinary' => ' VARBINARY(?)',
        'char' => ' CHAR(?)',
        'json' => ' JSON',
        'nchar' => ' NCHAR(?)',
        'nvarchar' => ' NVARCHAR(?)',
        'varchar' => ' VARCHAR(?)',
        'longtext' => ' LONGTEXT',
        'mediumtext' => ' MEDIUMTEXT',
        'text' => ' TEXT(?)',
        'tinytext' => ' TINYTEXT',
        'enum' => ' ENUM(?)',
        'date' => ' DATE',
        'time' => ' TIME',
        'timestamp' => ' TIMESTAMP',
        'datetime' => ' DATETIME',
        'alter' => ' ALTER',
        'add' => ' ADD',
        'constraint' => ' CONSTRAINT',
        'key' => ' KEY',
        'foreign' => ' FOREIGN',
        'references' => ' REFERENCES',
        'restrict' => ' RESTRICT'
    ];

    public function testMySQLKeywordsExist(): void
    {
        $this->assertSame(self::MYSQL_KEYWORDS_VALUES, self::MYSQL_KEYWORDS);
    }
}
