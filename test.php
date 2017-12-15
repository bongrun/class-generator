<?php

include 'src/Method.php';
include 'src/Template.php';

$template = new \bongrun\generator\Template(
    'bongrun\\test',
    'TestClass',
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
    [
        'bongrun\\test\\User',
        'bongrun\\test\\UserInterface',
    ],
    [
        'Тестовый класс'
    ],
    'User',
    [
        'UserInterface'
    ]);

file_put_contents('class.php', $template->getCode());