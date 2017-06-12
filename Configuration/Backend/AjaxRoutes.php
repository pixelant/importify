<?php

return [
    'unique_identifier' => [
        'path' => '/unique/identifier',
        'target' => \Pixelant\Importify\Controller\ImportController::class . '::getTableContentAction'
    ],
    'parse_file' => [
        'path' => '/parse/file',
        'target' => \Pixelant\Importify\Controller\ImportController::class . '::parseFile'
    ]
];
