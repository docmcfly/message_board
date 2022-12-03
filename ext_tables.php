<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Cylancer.MessageBoard',
            'Board',
            'LLL:EXT:message_board/Resources/Private/Language/locallang_be_board.xlf:plugin.name',
            'EXT:message_board/Resources/Public/Icons/messageboard_plugin_board.svg'
            );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Cylancer.MessageBoard',
            'Settings',
            'LLL:EXT:message_board/Resources/Private/Language/locallang_be_settings.xlf:plugin.name',
            'EXT:message_board/Resources/Public/Icons/messageboard_plugin_settings.svg'
            );
}
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('message_board', 'Configuration/TypoScript', 'MessageBoard');
