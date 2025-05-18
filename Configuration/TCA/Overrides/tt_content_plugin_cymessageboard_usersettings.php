<?php

use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

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

$extension = 'cyMessageboard';
$extensionDir = 'cy_messageboard';

$plugin = 'userSettings';

$signatur = strtolower("{$extension}_{$plugin}");
$iconIdentifier = "{$extension}-{$plugin}";

$translationPath = "LLL:EXT:{$extensionDir}/Resources/Private/Language/locallang_be_{$plugin}.xlf:";

ExtensionManagementUtility::addPlugin(
    new SelectItem(
        'select',
        $translationPath . 'plugin.name',
        $signatur,
        $iconIdentifier,
        $extension,
        $translationPath . 'plugin.description',
    ),
    'CType',
    $extension
);

