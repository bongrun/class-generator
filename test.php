<?php

include 'vendor/autoload.php';

$generator = new \bongrun\generator\Generator(__DIR__ . '/src/test', 'bongrun\generator\test', true);
$generator->run('TestClass',
    [
        new \bongrun\generator\Method([
            'name' => 'nameFunc',
            'codeLines' => [
                'return 1;'
            ],
        ]),
        new \bongrun\generator\Method([
            'name' => 'nameFunc2',
            'comments' => [
                'Первоя строка',
                'Вторая строка',
            ],
            'codeLines' => [
                '$a = 5;',
                'return $a;',
            ],
            'params' => [
                [
                    'type' => 'string',
                    'name' => '$var1',
                ],
                [
                    'type' => 'array',
                    'name' => '$var2',
                ],
            ],
            'type' => 'private',
            'returnType' => 'int',
        ]),
    ],
    [
        [
            'type' => 'protected',
            'name' => '$nameVar',
            'comments' => [
                'Тестовая переменная',
            ]
        ],
    ],
    [],
    [
        \bongrun\generator\test\User::class,
        \bongrun\generator\test\UserInterface::class,
    ],
    [
        'Тестовый класс'
    ],
    'User',
    [
        'UserInterface'
    ]);