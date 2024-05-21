<?php
namespace Cylancer\MessageBoard\Controller;

use Cylancer\MessageBoard\Domain\Model\Settings;
use Cylancer\MessageBoard\Domain\Model\FrontendUser;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use Cylancer\MessageBoard\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Cylancer\MessageBoard\Service\FrontendUserService;
 
/**
 * This file is part of the "MessageBoard" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024 C. Gogolin <service@cylancer.net>
 *
 * @package Cylancer\MessageBoard\Controller
 */
class SettingsController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /** @var FrontendUserService */
    private $frontendUserService = null;

    /** @var PersistenceManager */
    private $persistenceManager;

    /** @var FrontendUserRepository */
    private $frontendUserRepository = null;

    public function __construct(FrontendUserService $frontendUserService, PersistenceManager $persistenceManager, FrontendUserRepository $frontendUserRepository)
    {
        $this->frontendUserService = $frontendUserService;
        $this->persistenceManager = $persistenceManager;
        $this->frontendUserRepository = $frontendUserRepository;
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

    /**
     *
     * @param Settings $settings
     */
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