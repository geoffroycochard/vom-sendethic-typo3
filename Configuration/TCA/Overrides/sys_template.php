<?php

defined('TYPO3') or die();

// Ajouter le fichier statique TypoScript pour l'extension Sendethic
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'sendethic_typo3',
    'Configuration/TypoScript',
    'Sendethic Newsletter Unsubscribe'
); 