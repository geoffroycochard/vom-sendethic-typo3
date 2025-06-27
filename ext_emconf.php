<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "sendethic_typo3"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Sendethic Newsletter Unsubscribe',
    'description' => 'Plugin TYPO3 pour la gestion des désabonnements newsletter via l\'API Sendethic. 
    Permet la désinscription par segment et la suppression complète des données (RGPD).',
    'category' => 'plugin',
    'author' => 'Orléans Métropole',
    'author_email' => 'contact@orleans-metropole.fr',
    'author_company' => 'Orléans Métropole',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'lockType' => '',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
]; 