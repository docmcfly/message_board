<?php
use Cylancer\CyMessageboard\Controller\BoardController;
use Cylancer\CyMessageboard\Controller\SettingsController;
use Cylancer\CyMessageboard\Task\MessageBoardInformationAdditionalFieldProvider;
use Cylancer\CyMessageboard\Task\MessageBoardInformationTask;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

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

ExtensionUtility::configurePlugin( //
    'CyMessageboard', //
    'Messageboard', //
    [
        BoardController::class => 'show, save, remove'
    ],
    // non-cacheable actions
    [
        BoardController::class => 'show, save, remove'
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin( //
    'CyMessageboard', //
    'UserSettings', //
    [
        SettingsController::class => 'show, save'
    ],
    // non-cacheable actions
    [
        SettingsController::class => 'show, save'
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);


// Add task for optimizing database tables
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][MessageBoardInformationTask::class] = [
    'extension' => 'messageboard',
    'title' => 'LLL:EXT:cy_messageboard/Resources/Private/Language/locallang.xlf:task.messageBoardInformation.title',
    'description' => 'LLL:EXT:cy_messageboard/Resources/Private/Language/locallang.xlf:task.messageBoardInformation.description',
    'additionalFields' => MessageBoardInformationAdditionalFieldProvider::class
];

// E-Mail-Templates
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths']['cy_messageboard'] = 'EXT:cy_messageboard/Resources/Private/Templates/MessageBoardInfoMail/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths']['cy_messageboard'] = 'EXT:cy_messageboard/Resources/Private/Layouts/MessageBoardInfoMail/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['partialRootPaths']['cy_messageboard'] = 'EXT:cy_messageboard/Resources/Private/Partials/MessageBoardInfoMail/';




