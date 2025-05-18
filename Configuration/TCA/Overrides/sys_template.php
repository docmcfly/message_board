<?php
declare(strict_types=1);
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility; 

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

ExtensionManagementUtility::addStaticFile('message_board', 'Configuration/TypoScript', 'MessageBoard');
