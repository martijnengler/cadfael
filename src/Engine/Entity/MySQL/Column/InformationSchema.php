<?php

declare(strict_types=1);

namespace Cadfael\Engine\Entity\MySQL\Column;

/**
 * Class InformationSchema
 * @package Cadfael\Engine\Entity\MySQL\Column
 * @codeCoverageIgnore
 *
 * DTO of a record from information_schema.COLUMN
 */
class InformationSchema
{
    const KEYS = [
        '',
        'MUL',
        'PRI',
        'UNI'
    ];

    const DATA_TYPES = [
        'bigint',
        'binary',
        'bit',
        'blob',
        'char',
        'date',
        'datetime',
        'decimal',
        'double',
        'enum',
        'float',
        'geometry',
        'geometrycollection',
        'int',
        'json',
        'linestring',
        'longblob',
        'longtext',
        'mediumblob',
        'mediumint',
        'mediumtext',
        'multilinestring',
        'multipoint',
        'multipolygon',
        'point',
        'polygon',
        'set',
        'smallblob',
        'smallint',
        'text',
        'time',
        'timestamp',
        'tinyblob',
        'tinyint',
        'tinytext',
        'varbinary',
        'varchar',
        'year',
    ];

    public int $ordinal_position;
    public ?string $default;
    public bool $is_nullable = false;
    public string $column_type;
    public string $data_type;
    public string $column_key = '';
    public int $character_maximum_length = 0;
    public int $character_octet_length = 0;
    public int $numeric_precision;
    public int $datetime_precision;
    public ?string $character_set_name;
    public ?string $collation_name;
    public string $extra = '';
    public ?string $privileges;
    public ?string $column_comment;
    public ?string $generation_expression;

    /**
     * @param array<string> $schema This is a raw record from information_schema.COLUMN
     * @return InformationSchema
     */
    public static function createFromInformationSchema(array $schema)
    {
        $informationSchema = new InformationSchema();
        $informationSchema->ordinal_position = (int)$schema['ORDINAL_POSITION'];
        $informationSchema->default = $schema['COLUMN_DEFAULT'];
        $informationSchema->is_nullable  = $schema['IS_NULLABLE'] === 'YES';
        $informationSchema->column_type = $schema['COLUMN_TYPE'];
        $informationSchema->data_type = $schema['DATA_TYPE'];
        $informationSchema->column_key = $schema['COLUMN_KEY'];
        $informationSchema->character_maximum_length  = (int)$schema['CHARACTER_MAXIMUM_LENGTH'];
        $informationSchema->character_octet_length  = (int)$schema['CHARACTER_OCTET_LENGTH'];
        $informationSchema->numeric_precision = (int)$schema['NUMERIC_PRECISION'];
        $informationSchema->datetime_precision = (int)$schema['DATETIME_PRECISION'];
        $informationSchema->character_set_name = $schema['CHARACTER_SET_NAME'];
        $informationSchema->collation_name = $schema['COLLATION_NAME'];
        $informationSchema->extra  = $schema['EXTRA'];
        $informationSchema->privileges = $schema['PRIVILEGES'];
        $informationSchema->column_comment = $schema['COLUMN_COMMENT'];
        $informationSchema->generation_expression = $schema['GENERATION_EXPRESSION'];
        return $informationSchema;
    }
}
