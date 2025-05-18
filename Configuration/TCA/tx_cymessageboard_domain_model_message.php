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
        'iconfile' => 'EXT:cy_messageboard/Resources/Public/Icons/tx_cymessageboard_domain_model_message.gif',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'enablecolumns' => [
        ],
        'searchFields' => 'text',
        
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ]
                ],
                'default' => 0,
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 0,
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_cymessageboard_domain_model_message',
                'foreign_table_where' => 'AND {#tx_cymessageboard_domain_model_message}.{#pid}=###CURRENT_PID### AND {#tx_cymessageboard_domain_model_message}.{#sys_language_uid} IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
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
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'dbType' => 'datetime',
                'default' => time(),
                'readOnly' => true,
            ],
        ],
        'expiry_date' => [
            'label' => 'LLL:EXT:cy_messageboard/Resources/Private/Language/locallang_db.xlf:tx_cymessageboard_domain_model_message.expiryDate',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'dbType' => 'datetime',
                'default' => time() + 86400 * 30,
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
                    '1' => [
                        '0' => 'LLL:EXT:lang/locallang_core.xlf:labels.enabled'
                    ]
                ],
                'readOnly' => true,
            ]
        ],
       
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, timestamp, expiry_date, user, text',
    ],
    'types' => [
        '0' => ['showitem' => 'timestamp,  expiry_date,  user, text'],
    ],
];
