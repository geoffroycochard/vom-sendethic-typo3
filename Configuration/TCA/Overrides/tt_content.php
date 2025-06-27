<?php

defined('TYPO3') or die();

// Configuration du plugin NewsletterUnsubscribe
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'SendethicTypo3',
    'NewsletterUnsubscribe',
    'Newsletter - Désabonnement'
);

// Configuration des champs du plugin
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sendethictypo3_newsletterunsubscribe'] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sendethictypo3_newsletterunsubscribe'] = 'layout,select_key,pages,recursive';

// Configuration du FlexForm
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'sendethictypo3_newsletterunsubscribe',
    'FILE:EXT:sendethic_typo3/Configuration/FlexForms/NewsletterUnsubscribe.xml'
); 