<?php
namespace Cylancer\MessageBoard\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Context\Context;
use Cylancer\MessageBoard\Domain\Repository\FrontendUserRepository;
use Cylancer\MessageBoard\Domain\Model\FrontendUser;

/**
 * This file is part of the "message board" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024 C. Gogolin <service@cylancer.net>
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
     * @param object $obj
     * @return int
     */
    public static function getUid(object $object): int
    {
        return $object->getUid();
    }

    /**
     *
     * @return FrontendUser Returns the current frontend user
     */
    public function getCurrentUser(): ?FrontendUser
    {
        if (!$this->isLogged()) {
            return null;
        }
        return $this->frontendUserRepository->findByUid($this->getCurrentUserUid());
    }

    /**
     *
     * @return int
     */
    public function getCurrentUserUid(): int
    {
        if (!$this->isLogged()) {
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


}