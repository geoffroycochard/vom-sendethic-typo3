<?php

defined('TYPO3') or die();

return [
    'frontend' => [
        'orleans-metropole/sendethic-newsletter' => [
            'target' => \OrleansMetropole\SendethicTypo3\Middleware\SendethicNewsletterMiddleware::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
        ],
    ],
]; 