<?php
namespace Cylancer\MessageBoard\Task;

use TYPO3\CMS\Core\Context\Context;
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


    // ------------------------------------------------------
    // input fields
    const MESSAGE_BOARD_STORAGE_UID = 'messageBoardStorageUid';
    const MESSAGE_BOARD_URL = 'messageBoardUrl';

    public $messageBoardStorageUid = 0;
    public $messageBoardUrl = 'https://';


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
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

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
        while ($row = $s->fetchAssociative()) {
            $feUserStorageUids[] = $row['uid'];
        }

        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class, GeneralUtility::makeInstance(Context::class));


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

            // UPDATE tx_messageboard_domain_model_message SET expiry_date = ADDDATE(timestamp, 30);
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages')->createQueryBuilder();
            $queryBuilder->update('tx_messageboard_domain_model_message')
                ->set('expiry_date', 'ADDDATE(timestamp, 60 )', false)
                ->where($queryBuilder->expr()->isNull('expiry_date'))
                ->execute();
            $this->persistenceManager->persistAll();

            $users = [];
            /**
             *
             * @var FrontendUser $user
             * @var Message $messeage
             */
            // Attation: ->findByInfoMailWhenMessageBoardChanged() <- is a "magic" method.
            foreach ($this->frontendUserRepository->findByInfoMailWhenMessageBoardChanged(true) as $user) {
                if (!empty($user->getEmail())) {
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

            // delete old messages
            foreach ($this->messageRepository->findAllExpired() as $msg) {
                $this->messageRepository->remove($msg);
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
                'user' => $user,
                'url' => $this->messageBoardUrl
            ];

            $this->emailSendService->sendTemplateEmail($recipient, $sender, [], $subject, 'MessageBoardInfoMail', MessageBoardInformationTask::EXTENSION_NAME, $data);
        }
    }

    /**
     * This method returns the sleep duration as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation(): string
    {
        return 'Message board storage uid: ' . $this->messageBoardStorageUid
            . ' Message board url: ' . $this->messageBoardUrl;
    }

    /**
     *
     * @param string $key
     * @throws \Exception
     * @return number|string
     */
    public function get(string $key)
    {
        switch ($key) {
            case MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID:
                return $this->messageBoardStorageUid;
            case MessageBoardInformationTask::MESSAGE_BOARD_URL:
                return $this->messageBoardUrl;
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
    public function set(string $key, $value)
    {
        switch ($key) {
            case MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID:
                $this->messageBoardStorageUid = $value;
                break;
            case MessageBoardInformationTask::MESSAGE_BOARD_URL:
                $this->messageBoardUrl = $value;
                break;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }
}


