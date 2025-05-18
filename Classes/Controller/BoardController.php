<?php
namespace Cylancer\CyMessageboard\Controller;

use Cylancer\CyMessageboard\Domain\Model\Message;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Cylancer\CyMessageboard\Service\FrontendUserService;
use Cylancer\CyMessageboard\Domain\Repository\MessageRepository;

/**
 * This file is part of the "MessageBoard" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */
class BoardController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    
    public function __construct(
        private readonly FrontendUserService $frontendUserService,
        private readonly MessageRepository $messageRepository
    ) {
    }

    /**
     * @return ResponseInterface
     */
    public function showAction(): ResponseInterface
    {

        if ($this->frontendUserService->isLogged()) {
            $currentUser = $this->frontendUserService->getCurrentUser();
            $currentUserUid = $currentUser->getUid();
            $messages = [];
            $currentUserMessage = null;
            $isCurrentUserMessagePersistent = true;

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
        return $this->htmlResponse();
    }

    public function saveAction(Message $currentUserMessage): ResponseInterface
    {
        $currentUser = $this->frontendUserService->getCurrentUser();

        $text = trim($currentUserMessage->getText());
        if (empty($text)) {
            foreach ($this->messageRepository->findByUser($currentUser) as $msg) {
                $this->messageRepository->remove($msg);
            }
        } else {

            $msg = $this->messageRepository->findOneByUser($currentUser);
            $update = $msg != null;

            if (!$update) {
                $msg = new Message();
            }
            $msg->setUser($currentUser);
            $msg->setText($text);
            $msg->setTimestamp(new \DateTime());
            $msg->setChanged($msg->_isDirty('text'));
            $tmp = new \DateTime($msg->getTimestamp()->format('Y-m-d H:i:s'));
            $tmp->add(new \DateInterval('P' . $this->getOnlineTime() . 'D'));
            $msg->setExpiryDate($tmp);
            debug($msg);
            if ($update) {
                $this->messageRepository->update($msg);
            } else {
                $this->messageRepository->add($msg);
            }
        }
        return $this->redirect("show");
    } 
    private function getOnlineTime(): int
    {
        return isset($this->settings['onlineTime']) ? intval($this->settings['onlineTime']) : 30;
    } 
     public function removeAction(): ResponseInterface
    {
        $currentUser = $this->frontendUserService->getCurrentUser();
        foreach ($this->messageRepository->findByUser($currentUser) as $msg) {
            $this->messageRepository->remove($msg);
        }
        return $this->redirect("show");
    }
}