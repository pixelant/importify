<?php
$EM_CONF[$_EXTKEY] = [
  'title' => 'Importify',
  'description' => 'A generic importer for TYPO3',
  'category' => 'be',
  'author' => 'Tim Ta',
  'author_email' => 'tim.ta@pixelant.se',
  'state' => 'alpha',
  'internal' => '',
  'uploadfolder' => '0',
  'createDirs' => '',
  'clearCacheOnLoad' => 1,
  'version' => '0.0.0',
  'constraints' => [
    'depends' => [
      'typo3' => '8.7.0-8.9.99',
    ],
    'conflicts' => [
    ],
    'suggest' => [
    ],
  ],
  'autoload' => [
    'classmap' => [
      'Classes'
    ]
  ]
];
