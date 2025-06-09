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

return [
    'ctrl' => [
        'title' => 'LLL:EXT:cy_messageboard/Resources/Private/Language/locallang_db.xlf:tx_cymessageboard_domain_model_message',
        'label' => 'user',
        'iconfile' => 'EXT:cy_messageboard/Resources/Public/Icons/tx_cymessageboard_domain_model_message.svg',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'enablecolumns' => [
        ],
        'searchFields' => 'text',

    ],
    'columns' => [
        'text' => [
            'label' => 'LLL:EXT:cy_messageboard/Resources/Private/Language/locallang_db.xlf:tx_cymessageboard_domain_model_message.text',
            'config' => [
                'type' => 'text',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'timestamp' => [
            'label' => 'LLL:EXT:cy_messageboard/Resources/Private/Language/locallang_db.xlf:tx_cymessageboard_domain_model_message.timestamp',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'eval' => 'datetime',
                'dbType' => 'datetime',
                'default' => time(),
                'readOnly' => true,
            ],
        ],
        'expiry_date' => [
            'label' => 'LLL:EXT:cy_messageboard/Resources/Private/Language/locallang_db.xlf:tx_cymessageboard_domain_model_message.expiryDate',
            'config' => [
                'type' => 'datetime',
                'renderType' => 'datetime',
                'eval' => 'datetime',
                'dbType' => 'datetime',
                'default' => time() + (3600 * 24 * 30), // plus 30 days
                'readOnly' => true,
            ],
        ],
        'user' => [
            'label' => 'LLL:EXT:cy_messageboard/Resources/Private/Language/locallang_db.xlf:tx_cymessageboard_domain_model_message.user',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'minitems' => 1,
                'maxitems' => 1,
                'readOnly' => true,
            ],
        ],
        'changed' => [
            'label' => 'LLL:EXT:cy_messageboard/Resources/Private/Language/locallang_db.xlf:tx_cymessageboard_domain_model_message.changed',
            'config' => [
                'type' => 'check',
                'items' => [
                    [
                        'label' => 'LLL:EXT:lang/locallang_core.xlf:labels.enabled'
                    ]
                ],
                'readOnly' => true,
            ]
        ],

    ],
    'types' => [
        '0' => ['showitem' => 'timestamp,  expiry_date,  user, text, changed'],
    ],
];
