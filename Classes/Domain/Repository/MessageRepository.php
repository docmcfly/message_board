<?php

namespace Cylancer\MessageBoard\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class MessageRepository extends Repository
{
    protected $defaultOrderings = array(
        'timestamp' => QueryInterface::ORDER_DESCENDING,
    );
    
//     public function findMessages(){
//         $q = $this->createQuery();
//         $q->matching($q->logicalNot($q->equals('text', '')));
//         $q->setOrderings(['timestamp' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING]);
//         return $q->execute();
//     }
}