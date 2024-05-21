<?php
use Cylancer\MessageBoard\Controller\BoardController;
use Cylancer\MessageBoard\Controller\SettingsController;
use Cylancer\MessageBoard\Task\MessageBoardInformationAdditionalFieldProvider;
use Cylancer\MessageBoard\Task\MessageBoardInformationTask;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    ExtensionUtility::configurePlugin( //
    'MessageBoard', //
    'Board', //
    [ 
        BoardController::class => 'show, save, remove'
    ], 
        // non-cacheable actions
        [
            BoardController::class => 'show, save, remove'
        ]);
    ExtensionUtility::configurePlugin( //
    'MessageBoard', //
    'Settings', //
    [
        SettingsController::class => 'show, save'
    ], 
        // non-cacheable actions
        [
            SettingsController::class => 'show, save'
        ]);

    // wizards
    ExtensionManagementUtility::addPageTSConfig('mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    messageboard-plugin-board {
                        iconIdentifier = messageboard-plugin-board
                        title = LLL:EXT:message_board/Resources/Private/Language/locallang_be_board.xlf:plugin.name
                        description = LLL:EXT:message_board/Resources/Private/Language/locallang_be_board.xlf:plugin.description
                        tt_content_defValues {
                            CType = list
                            list_type = messageboard_board
                        }
                    }
                    messageboard-plugin-settings {
                        iconIdentifier = messageboard-plugin-settings
                        title = LLL:EXT:message_board/Resources/Private/Language/locallang_be_settings.xlf:plugin.name
                        description = LLL:EXT:message_board/Resources/Private/Language/locallang_be_settings.xlf:plugin.description
                        tt_content_defValues {
                            CType = list
                            list_type = messageboard_settings
                        }
                    }
                   
                }
                show = *
            }
       }');

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon('messageboard-plugin-board', \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, [
        'source' => 'EXT:message_board/Resources/Public/Icons/messageboard_plugin_board.svg'
    ]);
    $iconRegistry->registerIcon('messageboard-plugin-settings', \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, [
        'source' => 'EXT:message_board/Resources/Public/Icons/messageboard_plugin_settings.svg'
    ]);
});

// Add task for optimizing database tables
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][MessageBoardInformationTask::class] = [
    'extension' => 'messageboard',
    'title' => 'LLL:EXT:message_board/Resources/Private/Language/locallang.xlf:task.messageBoardInformation.title',
    'description' => 'LLL:EXT:message_board/Resources/Private/Language/locallang.xlf:task.messageBoardInformation.description',
    'additionalFields' => MessageBoardInformationAdditionalFieldProvider::class
];
    
// E-Mail-Templates
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths']['message_board']    = 'EXT:message_board/Resources/Private/Templates/MessageBoardInfoMail/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths']['message_board']    = 'EXT:message_board/Resources/Private/Layouts/MessageBoardInfoMail/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['partialRootPaths']['message_board']    = 'EXT:message_board/Resources/Private/Partials/MessageBoardInfoMail/';

    


