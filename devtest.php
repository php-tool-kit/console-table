<?php

require 'vendor/autoload.php';

$dataUnicode = [
    [
        'title' => 'Misery',
        'resume' => 'Paul Sheldon é um famoso escritor reconhecido pela série de best-sellers protagonizados por Misery Chastain.'
    ],
    [
        'title' => 'A revolução dos bichos: Um conto de fadas',
        'resume' => 'Verdadeiro clássico moderno, concebido por um dos mais influentes escritores do século XX, A revolução dos bichos é uma fábula sobre o poder. Narra a insurreição dos animais de uma granja contra seus donos. Progressivamente, porém, a revolução degenera numa tirania ainda mais opressiva que a dos humanos.'
    ],
    [
        'title' => 'O poder do hábito',
        'resume' => 'Durante os últimos dois anos, uma jovem transformou quase todos os aspectos de sua vida.'
    ]
];

$dataNoUnicode = [
    [
        'title' => 'Misery',
        'resume' => 'Paul Sheldon e um famoso escritor reconhecido pela serie de best-sellers protagonizados por Misery Chastain.'
    ],
    [
        'title' => 'A revolução dos bichos: Um conto de fadas',
        'resume' => 'Verdadeiro classico moderno, concebido por um dos mais influentes escritores do seculo XX, A revolucao dos bichos e uma fabula sobre o poder. Narra a insurreicao dos animais de uma granja contra seus donos. Progressivamente, porém, a revolucao degenera numa tirania ainda mais opressiva que a dos humanos.'
    ],
    [
        'title' => 'O poder do habito',
        'resume' => 'Durante os ultimos dois anos, uma jovem transformou quase todos os aspectos de sua vida.'
    ]
];

$tbl1 = new \PTK\Console\Table\Table($dataUnicode);

echo $tbl1;