<?php

return [
    'unique_identifier' => [
        'path' => '/unique/identifier',
        'target' => \Pixelant\Importify\Controller\ImportController::class . '::getTableContentAction'
    ],
    'import_file' => [
        'path' => '/import/file',
        'target' => \Pixelant\Importify\Controller\ImportController::class . '::importFileAction'
    ]
];
