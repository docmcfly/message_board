<?php

call_user_func(
    function ()
    {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'MessageBoard',
            'board',
            'LLL:EXT:message_board/Resources/Private/Language/locallang_be_board.xlf:plugin.name',
            // 'LLL:EXT:message_board/Resources/Private/Language/locallang_be_board.xlf:plugin.description',
            'EXT:message_board/Resources/Public/Icons/messageboard_plugin_board.svg'
            );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'MessageBoard',
            'settings',
            'LLL:EXT:message_board/Resources/Private/Language/locallang_be_settings.xlf:plugin.name',
            // 'LLL:EXT:message_board/Resources/Private/Language/locallang_be_settings.xlf:plugin.description',
            'EXT:message_board/Resources/Public/Icons/messageboard_plugin_settings.svg'
            
            );
        
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['messageboard_board'] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
            // plugin signature: <extension key without underscores> '_' <plugin name in lowercase>
            'messageboard_board',
            // Flexform configuration schema file
            'FILE:EXT:message_board/Configuration/FlexForms/MessageBoard.xml'
            );
        
}
);
