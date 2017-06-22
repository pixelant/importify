<?php

return [
    'get_column_name' => [
        'path' => '/get/column/name',
        'target' => \Pixelant\Importify\Controller\ImportController::class . '::getTableColumnNameAction'
    ],
    'import_file' => [
        'path' => '/import/file',
        'target' => \Pixelant\Importify\Controller\ImportController::class . '::importFileAction'
    ]
];
