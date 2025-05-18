<?php
namespace Cylancer\CyMessageboard\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Context\Context;
use Cylancer\CyMessageboard\Domain\Repository\FrontendUserRepository;
use Cylancer\CyMessageboard\Domain\Model\FrontendUser;

/**
 * This file is part of the "message board" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */
class FrontendUserService implements SingletonInterface
{

    public function __construct(
        private readonly FrontendUserRepository $frontendUserRepository)
    {
    }

    public static function getUid(object $object): int
    {
        return $object->getUid();
    }

    public function getCurrentUser(): ?FrontendUser
    {
        if (!$this->isLogged()) {
            return null;
        }
        return $this->frontendUserRepository->findByUid($this->getCurrentUserUid());
    }

    public function getCurrentUserUid(): int
    {
        if (!$this->isLogged()) {
            return false;
        }
        $context = GeneralUtility::makeInstance(Context::class);
        return $context->getPropertyFromAspect('frontend.user', 'id');
    }

    public function isLogged(): bool
    {
        $context = GeneralUtility::makeInstance(Context::class);
        return $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
    }


}