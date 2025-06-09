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

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$translationPath = 'LLL:EXT:cy_messageboard/Resources/Private/Language/locallang_db.xlf:tx_cymessageboard_domain_model_user';

ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    [

        'info_mail_when_message_board_changed' => [
            'label' => "$translationPath.info_mail_when_message_board_changed",
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                    ]
                ],
                'readOnly' => true,
            ]
        ],

    ]
);

ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    "--div--;$translationPath.tab_settings, info_mail_when_message_board_changed"
);
