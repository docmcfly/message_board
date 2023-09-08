<?php
namespace Cylancer\MessageBoard\Controller;

use Cylancer\MessageBoard\Domain\Model\Message;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Cylancer\MessageBoard\Service\FrontendUserService;
use Cylancer\MessageBoard\Domain\Repository\MessageRepository;

/**
 * This file is part of the "MessageBoard" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin <service@cylancer.net>
 *
 * @package Cylancer\MessageBoard\Controller
 */
class BoardController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**  @var FrontendUserService */
    private $frontendUserService = null;

    /** @var MessageRepository  */
    private $messageRepository = null;

    public function __construct(FrontendUserService $frontendUserService, MessageRepository $messageRepository)
    {
        $this->frontendUserService = $frontendUserService;
        $this->messageRepository = $messageRepository;
    }

    public function showAction(): void
    {
     
        if ($this->frontendUserService->isLogged()) {
            /**
             *
             * @var Message $currentUserMessage
             */
            $currentUser = $this->frontendUserService->getCurrentUser();
            $currentUserUid = $currentUser->getUid();
            $messages = array();
            $currentUserMessage = null;
            $isCurrentUserMessagePersistent = true;
            /**
             *
             * @var Message $msg
             */
            foreach ($this->messageRepository->findAll() as $msg) {
                if ($msg->getUser()->getUid() == $currentUserUid) {
                    $currentUserMessage = $msg;
                }
                $messages[] = $msg;
            }

            if ($currentUserMessage == null) {
                $isCurrentUserMessagePersistent = false;
                $currentUserMessage = GeneralUtility::makeInstance(Message::class);
                $currentUserMessage->setUser($currentUser);
                $currentUserMessage->setTimestamp(new \DateTime());
            }

            $this->view->assign('isCurrentUserMessagePersistent', $isCurrentUserMessagePersistent);
            $this->view->assign('currentUserMessage', $currentUserMessage);
            $this->view->assign('messages', $messages);
            $this->view->assign('currentUser', $currentUser);
            
            $this->view->assign('userLink', $this->settings['userLink']);
        }
    }

    /**
     *
     * @param Message $currentUserMessage
     * @return void
     */
    public function saveAction(Message $currentUserMessage): void
    {
        $currentUser = $this->frontendUserService->getCurrentUser();

        $text = trim($currentUserMessage->getText());
        if (empty($text)) {
            foreach ($this->messageRepository->findByUser($currentUser) as $msg) {
                $this->messageRepository->remove($msg);
            }
        } else {
            /** @var Message $msg */
            $msg = $this->messageRepository->findOneByUser($currentUser);
            if ($msg != null) {
                if ($msg->_isDirty('text')) {
                    $msg->setText($text);
                    $msg->setTimestamp(new \DateTime());
                    $this->messageRepository->update($msg);
                }
            } else {
                $msg = new Message();
                $msg->setUser($currentUser);
                $msg->setText($text);
                $msg->setTimestamp(new \DateTime());
                $this->messageRepository->add($msg);
            }
        }
        $this->redirect("show");
    }

    /**
     *
     * @return void
     */
    public function removeAction(): void
    {
        $currentUser = $this->frontendUserService->getCurrentUser();
        foreach ($this->messageRepository->findByUser($currentUser) as $msg) {
            $this->messageRepository->remove($msg);
        }
        $this->redirect("show");
    }
}