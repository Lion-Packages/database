<?php

declare(strict_types=1);

namespace Tests\Provider;

trait FunctionsTraitProviderTrait
{
    const BULK_ROWS = [
        [1, 'Administrator'],
        [2, 'Client']
    ];

    public static function addCharacterBulkProvider(): array
    {
        return [
            [
                'isSchema' => false,
                'enableInsert' => false,
                'addQuotes' => false,
                'return' => '(?, ?), (?, ?)'
            ],
            [
                'isSchema' => true,
                'enableInsert' => true,
                'addQuotes' => false,
                'return' => "(1, Administrator), (2, Client)"
            ],
            [
                'isSchema' => true,
                'enableInsert' => true,
                'addQuotes' => true,
                'return' => "('1', 'Administrator'), ('2', 'Client')"
            ]
        ];
    }

    public static function addCharacterEqualToProvider(): array
    {
        return [
            [
                'columns' => [
                    'idroles' => 1
                ],
                'return' => 'idroles = ?',
                'isSchema' => false,
                'enableInsert' => false
            ],
            [
                'columns' => [
                    'idroles' => 1,
                    'roles_name' => 'Administrator'
                ],
                'return' => 'idroles = ?, roles_name = ?',
                'isSchema' => false,
                'enableInsert' => false
            ],
            [
                'columns' => [
                    'idroles' => 1,
                    'roles_name' => 'Administrator',
                    'roles_description' => 'role description'
                ],
                'return' => 'idroles = ?, roles_name = ?, roles_description = ?',
                'isSchema' => false,
                'enableInsert' => false
            ],
            [
                'columns' => [
                    'idroles' => '_idroles'
                ],
                'return' => 'idroles = _idroles',
                'isSchema' => true,
                'enableInsert' => true
            ],
            [
                'columns' => [
                    'idroles' => '_idroles',
                    'roles_name' => '_roles_name'
                ],
                'return' => 'idroles = _idroles, roles_name = _roles_name',
                'isSchema' => true,
                'enableInsert' => true
            ],
            [
                'columns' => [
                    'idroles' => '_idroles',
                    'roles_name' => '_roles_name',
                    'roles_description' => '_roles_description'
                ],
                'return' => 'idroles = _idroles, roles_name = _roles_name, roles_description = _roles_description',
                'isSchema' => true,
                'enableInsert' => true
            ]
        ];
    }

    public static function addCharacterAssocProvider(): array
    {
        return [
            [
                'columns' => [
                    'idroles' => 1
                ],
                'return' => '?'
            ],
            [
                'columns' => [
                    'idroles' => 1,
                    'roles_name' => 'Administrator'
                ],
                'return' => '?, ?'
            ],
            [
                'columns' => [
                    'idroles' => 1,
                    'roles_name' => 'Administrator',
                    'roles_description' => 'role description'
                ],
                'return' => '?, ?, ?'
            ]
        ];
    }

    public static function addCharacterProvider(): array
    {
        return [
            [
                'columns' => [1],
                'return' => '?'
            ],
            [
                'columns' => [1, 'Administrator'],
                'return' => '?, ?'
            ],
            [
                'columns' => [1, 'Administrator', 'role description'],
                'return' => '?, ?, ?'
            ]
        ];
    }

    public static function addColumnsProvider(): array
    {
        return [
            [
                'isSchema' => false,
                'enableInsert' => false,
                'columns' => [],
                'spacing' => true,
                'addQuotes' => false,
                'return' => '*'
            ],
            [
                'isSchema' => false,
                'enableInsert' => false,
                'columns' => ['idroles'],
                'spacing' => true,
                'addQuotes' => false,
                'return' => 'idroles'
            ],
            [
                'isSchema' => false,
                'enableInsert' => false,
                'columns' => ['idroles', 'roles_name'],
                'spacing' => true,
                'addQuotes' => false,
                'return' => 'idroles, roles_name'
            ],
            [
                'isSchema' => false,
                'enableInsert' => false,
                'columns' => ['idroles', 'roles_name', 'roles_description'],
                'spacing' => true,
                'addQuotes' => false,
                'return' => 'idroles, roles_name, roles_description'
            ]
        ];
    }

    public static function addEnumColumnsProvider(): array
    {
        return [
            [
                'columns' => [],
                'spacing' => true,
                'return' => '*'
            ],
            [
                'columns' => ['idroles'],
                'spacing' => true,
                'return' => "'idroles'"
            ],
            [
                'columns' => ['idroles', 'roles_name'],
                'spacing' => true,
                'return' => "'idroles', 'roles_name'"
            ],
            [
                'columns' => ['idroles', 'roles_name', 'roles_description'],
                'spacing' => true,
                'return' => "'idroles', 'roles_name', 'roles_description'"
            ]
        ];
    }
}
