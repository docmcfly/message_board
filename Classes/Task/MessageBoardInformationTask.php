<?php
namespace Cylancer\MessageBoard\Task;

use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Cylancer\MessageBoard\Domain\Repository\MessageRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use Cylancer\MessageBoard\Domain\Model\Message;
use Cylancer\MessageBoard\Domain\Repository\FrontendUserRepository;
use Cylancer\MessageBoard\Service\FrontendUserService;
use Cylancer\MessageBoard\Service\EmailSendService;
use Cylancer\MessageBoard\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class MessageBoardInformationTask extends AbstractTask
{

    /*
     * TODO:
     *
     * - UserRepository storage uid abfragen und verweden.
     * - Uid validieren
     * - Messages mit Update = true abfragen: Alle informieren die das möchten, außer dem Verfasser.
     * ( Multi - Infomail bauen)
     * - Update-Flag reseten.
     * - Bereinigem
     */

    // ------------------------------------------------------
    // input fields
    const MESSAGE_BOARD_STORAGE_UID = 'messageBoardStorageUid';

    public $messageBoardStorageUid = 0;

    // ------------------------------------------------------
    // debug switch
    const DISABLE_PERSISTENCE_MANAGER = false;

    const EXTENSION_NAME = 'MessageBoard';

    // ------------------------------------------------------

    /**  @var FrontendUserService */
    private $frontendUserService = null;

    /** @var FrontendUserRepository */
    private $frontendUserRepository = null;

    /** @var MessageRepository  */
    private $messageRepository = null;

    /** @var PageRepository */
    private $pageRepository = null;

    /** @var PersistenceManager  */
    private $persistenceManager = null;

    /** @var EmailSendService */
    private $emailSendService = null;

    private function initialize()
    {
        $this->messageBoardStorageUid = intval($this->messageBoardStorageUid);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->persistenceManager = $objectManager->get(PersistenceManager::class);

        $feUserStorageUids = [];
        /**
         *
         * @var QueryBuilder $qb
         */
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $s = $qb->select('uid')
            ->from('pages')
            ->where($qb->expr()
            ->eq('module', $qb->createNamedParameter('fe_users')))
            ->execute();
        while ($row = $s->fetch()) {
            $feUserStorageUids[] = $row['uid'];
        }

        $this->pageRepository = $objectManager->get(PageRepository::class);

        $this->frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class, $objectManager);
        $this->frontendUserRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->frontendUserRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds($feUserStorageUids);
        $this->frontendUserRepository->setDefaultQuerySettings($querySettings);

        $this->messageRepository = GeneralUtility::makeInstance(MessageRepository::class, $objectManager);
        $this->messageRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->messageRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds([
            $this->messageBoardStorageUid
        ]);
        $this->messageRepository->setDefaultQuerySettings($querySettings);

        $this->frontendUserService = GeneralUtility::makeInstance(FrontendUserService::class, $this->frontendUserRepository);

        $this->emailSendService = GeneralUtility::makeInstance(EmailSendService::class);
    }

    private function validate()
    {
        $valid = true;

        $valid &= $this->pageRepository != null;
        $valid &= $this->messageRepository != null;
        $valid &= $this->frontendUserRepository != null;

        $valid &= $this->isPageUidValid($this->messageBoardStorageUid);

        return $valid;
    }

    private function isPageUidValid(int $id)
    {
        return $this->pageRepository->getPage($id) != null;
    }

    public function execute()
    {
        $this->initialize();

        if ($this->validate()) {
            $users = [];
            /**
             *
             * @var FrontendUser $user
             * @var Message $messeage
             */
            // Attation: ->findByInfoMailWhenMessageBoardChanged() <- is "magic" method.
            foreach ($this->frontendUserRepository->findByInfoMailWhenMessageBoardChanged(true) as $user) {
                if (! empty($user->getEmail())) {
                    $users[] = $user;
                }
            }

            $isFirstMessage = true;
            foreach ($this->messageRepository->findByChanged(true) as $message) {
                if ($isFirstMessage) {
                    foreach ($users as $user) {
                        $this->sendInfoMail($user);
                    }

                    $isFirstMessage = false;
                }
                $message->setChanged(false);

                $this->messageRepository->update($message);
            }
            $this->persistenceManager->persistAll();
            return true;
        } else {
            return false;
        }
    }

    private function sendInfoMail(FrontendUser $user)
    {
        if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $recipient = [
                $user->getEmail() => $user->getFirstName() . ' ' . $user->getLastName()
            ];
            $sender = [
                MailUtility::getSystemFromAddress() => LocalizationUtility::translate('task.messageBoardInformation.updateMail.senderName', MessageBoardInformationTask::EXTENSION_NAME)
            ];
            $subject = LocalizationUtility::translate('task.messageBoardInformation.infoMail.subject', MessageBoardInformationTask::EXTENSION_NAME);

            $data = [
                'user' => $user
            ];

            $this->emailSendService->sendTemplateEmail($recipient, $sender, [], $subject, 'MessageBoardInfoMail', MessageBoardInformationTask::EXTENSION_NAME, $data);
        }
    }

    /**
     * This method returns the sleep duration as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation(): String
    {
        return 'Message board storage uid: ' . $this->messageBoardStorageUid;
    }

    /**
     *
     * @param string $key
     * @throws \Exception
     * @return number|string
     */
    public function get(String $key)
    {
        switch ($key) {
            case MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID:
                return $this->messageBoardStorageUid;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }

    /**
     *
     * @param string $key
     * @param string|number $value
     * @throws \Exception
     */
    public function set(String $key, $value)
    {
        switch ($key) {
            case MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID:
                $this->messageBoardStorageUid = $value;
                break;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }
}


