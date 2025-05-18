<?php

/**
 *
 * This file is part of the "Messageboard" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *       
 */


$EM_CONF[$_EXTKEY] = [
    'title' => 'A simple message board for the frontend users',
    'description' => 'The fe users can publish a message for other fe users.',
    'category' => 'plugin',
    'author' => 'C. Gogolin',
    'author_email' => 'service@cylancer.net',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => true,
    'version' => '4.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];


/* CHANGLOG: 
        4.0.2 :: FIX : Background task uses the wrong table name. 
        4.0.1 :: FIX : upgrade wizzard
        4.0.0 :: Update to TYPO3 13.4
        3.0.1 :: Fix missing flush the changes. 
        3.0.0 :: Update to TYPO3 12.4
        2.1.0 :: Add a expiry date / Update the UI.
        2.0.1 :: Fix the contact link. 
        2.0.0 :: Fix the plugin registration/configuration.
 */


