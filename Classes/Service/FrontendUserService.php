<?php
namespace Cylancer\MessageBoard\Service;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Context\Context;
use Cylancer\MessageBoard\Domain\Repository\FrontendUserRepository;
use Cylancer\MessageBoard\Domain\Model\FrontendUser;
use Cylancer\MessageBoard\Domain\Model\FrontendUserGroup;

/**
 * This file is part of the "message board" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin <service@cylancer.net>
 *
 * @package Cylancer\MessageBoard\Service
 */
class FrontendUserService implements SingletonInterface
{
    
    /** @var FrontendUserRepository   */
    private $frontendUserRepository = null;
    
    /**
     *
     * @param FrontendUserRepository $frontendUserRepository
     */
    public function __construct(FrontendUserRepository $frontendUserRepository)
    {
        $this->frontendUserRepository = $frontendUserRepository;
    }
    
    /**
     *
     * @param Object $obj
     * @return int
     */
    public static function getUid(Object $object): int
    {
        return $object->getUid();
    }
    
    /**
     *
     * @return FrontendUser Returns the current frontend user
     */
    public function getCurrentUser(): FrontendUser
    {
        if (! $this->isLogged()) {
            return false;
        }
        return $this->frontendUserRepository->findByUid($this->getCurrentUserUid());
    }
    
    /**
     *
     * @return int
     */
    public function getCurrentUserUid(): int
    {
        if (! $this->isLogged()) {
            return false;
        }
        $context = GeneralUtility::makeInstance(Context::class);
        return $context->getPropertyFromAspect('frontend.user', 'id');
    }
    
    /**
     * Check if the user is logged
     *
     * @return bool
     */
    public function isLogged(): bool
    {
        $context = GeneralUtility::makeInstance(Context::class);
        return $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
    }
    
    /**
     *
     * @param FrontendUserGroup $userGroup
     * @param integer $fegid
     * @param array $loopProtect
     * @return boolean
     */
    public function contains($userGroup, $feugid, &$loopProtect = array()): bool
    {
        if ($userGroup->getUid() == $feugid) {
            return true;
        } else {
            if (! in_array($userGroup->getUid(), $loopProtect)) {
                $loopProtect[] = $userGroup->getUid();
                foreach ($userGroup->getSubgroup() as $sg) {
                    if ($this->contains($sg, $feugid, $loopProtect)) {
                        return true;
                    }
                }
            }
            return false;
        }
    }
    
    /**
     *
     * @param FrontendUserGroup $userGroup
     * @param integer $fegid
     * @param array $loopProtect
     * @return boolean
     */
    public function getAllGroups($userGroup, $return = array(), &$loopProtect = array()): bool
    {
        $return = array();
        if ($userGroup->getUid() == $feugid) {
            return true;
        } else {
            if (! in_array($userGroup->getUid(), $loopProtect)) {
                $loopProtect[] = $userGroup->getUid();
                foreach ($userGroup->getSubgroup() as $sg) {
                    if ($this->contains($sg, $feugid, $loopProtect)) {
                        return true;
                    }
                }
            }
            return false;
        }
    }
    
    /**
     *
     * @param string $table
     * @return QueryBuilder
     */
    protected function getQueryBuilder(String $table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }
    
    /**
     * Returns an array with all subgroups of the frontend user to the root of groups...
     *
     * @param FrontendUser $frontendUser
     * @return array
     */
    public function getUserSubGroups(FrontendUser $frontendUser): array
    {
        $return = [];
        foreach ($frontendUser->getUsergroup() as $ug) {
            $return = array_merge($return, $this->_getSubgroups($ug, $return));
        }
        return $return;
    }
    
    /**
     * Returns an array with all subgroups of the frontend user group to the root of groups...
     *
     * @param FrontendUserGroup $frontendUserGroup
     * @return array
     */
    public function getSubGroups(FrontendUserGroup $frontendUserGroup): array
    {
        return $this->_getSubgroups($frontendUserGroup);
    }
    
    private function _getSubgroups(FrontendUserGroup $frontendUserGroup, array &$return = []): array
    {
        $return[] = $frontendUserGroup->getUid();
        foreach ($frontendUserGroup->getSubgroup() as $ug) {
            $uid = $ug->getUid();
            if (! in_array($uid, $return)) {
                $return = array_unique(array_merge($return, $this->_getSubgroups($ug, $return)));
            }
        }
        return $return;
    }
    
    /**
     * Returns all groups from the frontend user to all his leafs in the hierachy tree...
     *
     * @param FrontendUser $frontendUser
     * @return array
     */
    public function getUserTopGroups(FrontendUser $frontendUser): array
    {
        $return = [];
        foreach ($frontendUser->getUsergroup() as $ug) {
            $return = array_merge($return, $this->_getTopGroups($ug->getUid(), $return));
        }
        return $return;
    }
    
    /**
     * Returns all groups from the frontend user group to all his leafs in the hierachy tree...
     *
     * @param FrontendUserGroup $userGroup
     * @return array
     */
    public function getTopGroups(FrontendUserGroup $userGroup): array
    {
        return $this->_getTopGroups($userGroup->getUid());
    }
    
    private function _getTopGroups(int $ug, array &$return = []): array
    {
        $return[] = $ug;
        $qb = $this->getQueryBuilder('fe_groups');
        $s = $qb->select('fe_groups.uid')
        ->from('fe_groups')
        ->where($qb->expr()
            ->inSet('subgroup', $ug))
            ->execute();
            while ($row = $s->fetch()) {
                $uid = intVal($row['uid']);
                if (! in_array($uid, $return)) {
                    $return = array_unique(array_merge($return, $this->_getTopGroups($uid, $return)));
                }
            }
            return $return;
    }
    
    public function getInformFrontendUser(array $frontendUserGroupUids)
    {
        
        // debug($frontendUserGroupUids);
        $_frontendUserGroupUids = array();
        
        /**
         *
         * @var FrontendUserGroup $frontendUserGroup
         */
        foreach ($frontendUserGroupUids as $guid) {
            // debug($guid);
            $_frontendUserGroupUids = array_merge($frontendUserGroupUids, $this->getTopGroups($this->frontendUserGroupRepository->findByUid($guid)));
        }
        $_frontendUserGroupUids = array_unique($_frontendUserGroupUids);
        $qb = $this->getQueryBuilder('fe_user');
        $qb->select('uid')->from('fe_users');
        foreach ($_frontendUserGroupUids as $guid) {
            $qb->orWhere($qb->expr()
                ->inSet('usergroup', $guid));
        }
        $qb->andWhere($qb->expr()
            ->eq('info_mail_when_repeated_task_added', 1));
        // debug($qb->getSQL());
        $s = $qb->execute();
        $return = array();
        while ($row = $s->fetch()) {
            $return[] = intVal($row['uid']);
        }
        return $return;
    }
}
