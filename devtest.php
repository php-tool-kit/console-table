<?php

require 'vendor/autoload.php';

$tbl = new \PTK\Console\Table\Table([
    [
        'id' => 1,
        'nome' => 'John',
        'age' => 40
    ],
    [
        'id' => 2,
        'nome' => 'Mary',
        'age' => 25
    ],
    [
        'id' => 3,
        'nome' => 'Paul',
        'age' => 54
    ],
    [
        'id' => 4,
        'nome' => 'Ivy',
        'age' => 15
    ],
]);

$tbl
        ->setColModel(new \PTK\Console\Table\ColModel('id', align: \PTK\Console\Table\ColModel::ALIGN_LEFT))
        ->setColModel(new \PTK\Console\Table\ColModel('nome', width: 0.7, align: \PTK\Console\Table\ColModel::ALIGN_CENTER))
        ->setColModel(new \PTK\Console\Table\ColModel('age', width: 0.1, align: \PTK\Console\Table\ColModel::ALIGN_RIGHT))
        ->setTitle('Sample table')
    ;

echo $tbl;