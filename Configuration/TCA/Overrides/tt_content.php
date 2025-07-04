<?php

defined('TYPO3') or die();

// Configuration du plugin Unsubscribe
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'SendethicTypo3',
    'Unsubscribe',
    'Newsletter - Désabonnement'
);

// Configuration des champs du plugin
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sendethictypo3_unsubscribe'] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sendethictypo3_unsubscribe'] = 'layout,select_key,pages,recursive';

// Configuration du FlexForm
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'sendethictypo3_unsubscribe',
    'FILE:EXT:sendethic_typo3/Configuration/FlexForms/NewsletterUnsubscribe.xml'
); 