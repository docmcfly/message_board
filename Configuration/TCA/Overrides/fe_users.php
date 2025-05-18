<?php
defined('TYPO3') || die('Access denied.');

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

if (!isset($GLOBALS['TCA']['fe_users']['ctrl']['type'])) {
    // no type field defined, so we define it here. This will only happen the first time the extension is installed!!
    $GLOBALS['TCA']['fe_users']['ctrl']['type'] = 'tx_extbase_type';
    $tempColumnstx_cymessageboard_fe_users = [];
    $tempColumnstx_cymessageboard_fe_users[$GLOBALS['TCA']['fe_users']['ctrl']['type']] = [
        'exclude' => true,
        'label'   => 'LLL:EXT:messageBoard/Resources/Private/Language/locallang_db.xlf:tx_cymessageboard_domain_model_user.tx_extbase_type',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['',''],
                ['User','tx_cymessageboard_User']
            ],
            'default' => 'tx_cymessageboard_User',
            'size' => 1,
            'maxitems' => 1,
        ]
    ];
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumnstx_cymessageboard_fe_users);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    $GLOBALS['TCA']['fe_users']['ctrl']['type'],
    '',
    'after:' . $GLOBALS['TCA']['fe_users']['ctrl']['label']
);

$tmp_messageboard_columns = [
   
    'info_mail_when_message_board_changed' => [
        'label' => 'LLL:EXT:cy_messageboard/Resources/Private/Language/locallang_db.xlf:tx_cymessageboard_domain_model_user.info_mail_when_message_board_changed',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
            'readOnly' => true,
        ]
    ],
    
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users',$tmp_messageboard_columns);

/* inherit and extend the show items from the parent class */

if (isset($GLOBALS['TCA']['fe_users']['types']['0']['showitem'])) {
    $GLOBALS['TCA']['fe_users']['types']['tx_cymessageboard_User']['showitem'] = $GLOBALS['TCA']['fe_users']['types']['0']['showitem'];
} elseif(is_array($GLOBALS['TCA']['fe_users']['types'])) {
    // use first entry in types array
    $fe_users_type_definition = reset($GLOBALS['TCA']['fe_users']['types']);
    $GLOBALS['TCA']['fe_users']['types']['tx_cymessageboard_User']['showitem'] = $fe_users_type_definition['showitem'];
} else {
    $GLOBALS['TCA']['fe_users']['types']['tx_cymessageboard_User']['showitem'] = '';
}

$GLOBALS['TCA']['fe_users']['columns'][$GLOBALS['TCA']['fe_users']['ctrl']['type']]['config']['items'][] = ['LLL:EXT:cy_messageboard/Resources/Private/Language/locallang_db.xlf:tx_cymessageboard_domain_model_user.tx_extbase_type','tx_cymessageboard_User'];

$tmp_types = array_keys($GLOBALS['TCA']['fe_users']['types']);
foreach($tmp_types as $type){
    $GLOBALS['TCA']['fe_users']['types'][$type]['showitem'] .= ', --div--;LLL:EXT:cy_messageboard/Resources/Private/Language/locallang_db.xlf:tx_cymessageboard_domain_model_user.tab_settings, info_mail_when_message_board_changed ';
}

