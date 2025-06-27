<?php

defined('TYPO3') or die();

// Enregistrement de la commande CLI
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \OrleansMetropole\SendethicTypo3\Command\GenerateUnsubscribeLinksCommand::class; 