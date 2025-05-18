<?php
namespace Cylancer\CyMessageboard\Controller;

use Cylancer\CyMessageboard\Domain\Model\Settings;
use Cylancer\CyMessageboard\Domain\Model\FrontendUser;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use Cylancer\CyMessageboard\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Cylancer\CyMessageboard\Service\FrontendUserService;

/**
 * This file is part of the "MessageBoard" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */
class SettingsController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    public function __construct(
        private readonly FrontendUserService $frontendUserService,
        private readonly PersistenceManager $persistenceManager,
        private readonly FrontendUserRepository $frontendUserRepository
    ) {
    }

    public function showAction(): ResponseInterface
    {
        /** @var FrontendUser $u */
        $u = $this->frontendUserRepository->findByUid($this->frontendUserService->getCurrentUserUid());
        if ($u != null) {
            $s = new Settings();
            $s->setInfoMailWhenMessageBoardChanged($u->getInfoMailWhenMessageBoardChanged());
            $this->view->assign('settings', $s);
        }
        return $this->htmlResponse();
    }

    public function saveAction(Settings $settings)
    {
        /** @var FrontendUser $u */
        $u = $this->frontendUserRepository->findByUid($this->frontendUserService->getCurrentUserUid());
        if ($u != null) {
            $u->setInfoMailWhenMessageBoardChanged($settings->getInfoMailWhenMessageBoardChanged());
            $this->frontendUserRepository->update($u);
            $this->persistenceManager->persistAll();
        }
        return GeneralUtility::makeInstance(ForwardResponse::class, 'show');
    }
}