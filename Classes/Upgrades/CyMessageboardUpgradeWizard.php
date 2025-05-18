<?php

declare(strict_types=1);

namespace Cylancer\CyMessageboard\Upgrades;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

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

#[UpgradeWizard('cymessageboard_cymessageboardUpgradeWizard')]
final class CyMessageboardUpgradeWizard implements UpgradeWizardInterface
{

    private PersistenceManager $persistentManager;

    private ResourceFactory $resourceFactory;

    public function __construct()
    {
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
    }


    public function getTitle(): string
    {
        return 'Migration of the message board entries to the new database table';
    }

    public function getDescription(): string
    {
        return "Moves all old message board entries to the new database table ";
    }

    public function executeUpdate(): bool
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connectionPool
            ->getConnectionForTable('tx_cymessageboard_domain_model_message')
            ->prepare('INSERT INTO `tx_cymessageboard_domain_model_message` '
                . '( `uid`, `pid`, `tstamp`, `crdate`, `sys_language_uid`, `l10n_parent`, `l10n_state`, '
                . '`l10n_diffsource`, `t3ver_oid`, `t3ver_wsid`, `t3ver_state`, `t3ver_stage`, `user`, '
                . '`text`, `timestamp`, `changed`, `expiry_date`)'
                . ' SELECT '
                . ' `uid`, `pid`, `tstamp`, `crdate`, `sys_language_uid`, `l10n_parent`, `l10n_state`, '
                . '`l10n_diffsource`, `t3ver_oid`, `t3ver_wsid`, `t3ver_state`, `t3ver_stage`, `user`, '
                . '`text`, `timestamp`, `changed`, `expiry_date`'
                . ' FROM `tx_messageboard_domain_model_message`')->executeStatement();

        return true;
    }

    /**
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function updateNecessary(): bool
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool
            ->getConnectionForTable('tx_cymessageboard_domain_model_message')
            ->count(
                '*',
                'tx_cymessageboard_domain_model_message',
                [],
            ) == 0
            && $connectionPool
                ->getConnectionForTable('tx_messageboard_domain_model_message')
                ->count(
                    '*',
                    'tx_messageboard_domain_model_message',
                    [],
                ) > 0
        ;
    }

    /**
     * Returns an array of class names of prerequisite classes
     *
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [];
    }
}

