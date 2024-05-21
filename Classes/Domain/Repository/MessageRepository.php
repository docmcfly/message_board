<?php

namespace Cylancer\MessageBoard\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class MessageRepository extends Repository
{
    protected $defaultOrderings = array(
        'timestamp' => QueryInterface::ORDER_DESCENDING,
    );


    public function findAllExpired()
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