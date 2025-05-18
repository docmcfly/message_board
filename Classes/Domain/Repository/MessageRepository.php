<?php

namespace Cylancer\CyMessageboard\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
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
class MessageRepository extends Repository
{
    protected $defaultOrderings = [
        'timestamp' => QueryInterface::ORDER_DESCENDING,
    ];


    public function findAllExpired(): array|QueryResultInterface
    {
        $today = new \DateTime();
        $today = $today->format('Y-m-d');
        $query = $this->createQuery();
        $query->matching(
            $query->lessThanOrEqual('expiryDate', $today)
        );
        return $query->execute();
    }


}