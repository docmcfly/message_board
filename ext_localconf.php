<?php
use Cylancer\MessageBoard\Controller\BoardController;
use Cylancer\MessageBoard\Controller\SettingsController;

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin( //
    'Cylancer.MessageBoard', //
    'Board', //
    [ 
        BoardController::class => 'show, save, remove'
    ], 
        // non-cacheable actions
        [
            BoardController::class => 'show, save, remove'
        ]);
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin( //
    'Cylancer.MessageBoard', //
    'Settings', //
    [
        SettingsController::class => 'show, save'
    ], 
        // non-cacheable actions
        [
            SettingsController::class => 'show, save'
        ]);

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('mod {
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
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Cylancer\MessageBoard\Task\MessageBoardInformationTask::class] = [
    'extension' => 'messageboard',
    'title' => 'LLL:EXT:message_board/Resources/Private/Language/locallang.xlf:task.messageBoardInformation.title',
    'description' => 'LLL:EXT:message_board/Resources/Private/Language/locallang.xlf:task.messageBoardInformation.description',
    'additionalFields' => \Cylancer\MessageBoard\Task\MessageBoardInformationAdditionalFieldProvider::class
];
    
    


