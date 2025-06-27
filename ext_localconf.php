<?php

defined('TYPO3') or die();

// Configuration des plugins
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'SendethicTypo3',
    'NewsletterUnsubscribe',
    [
        \OrleansMetropole\SendethicTypo3\Controller\NewsletterUnsubscribeController::class => 'unsubscribeSegment,confirmSegmentUnsubscribe,unsubscribeAll,confirmDeleteAll,success,error'
    ],
    [
        \OrleansMetropole\SendethicTypo3\Controller\NewsletterUnsubscribeController::class => 'unsubscribeSegment,confirmSegmentUnsubscribe,unsubscribeAll,confirmDeleteAll,success,error'
    ]
);

// Configuration des routes
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = array_merge(
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] ?? [],
    ['tx_sendethictypo3_newsletterunsubscribe[token]']
); 