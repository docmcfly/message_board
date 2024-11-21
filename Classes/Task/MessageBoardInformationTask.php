<?php
namespace Cylancer\MessageBoard\Task;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Cylancer\MessageBoard\Domain\Repository\MessageRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use Cylancer\MessageBoard\Domain\Model\Message;
use Cylancer\MessageBoard\Domain\Repository\FrontendUserRepository;
use Cylancer\MessageBoard\Service\FrontendUserService;
use Cylancer\MessageBoard\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class MessageBoardInformationTask extends AbstractTask
{


    // ------------------------------------------------------
    // input fields
    const MESSAGE_BOARD_STORAGE_UID = 'messageBoardStorageUid';
    const MESSAGE_BOARD_PAGE_UID = 'messageBoardPageUid';
    const VALIDITY_PERIOD = 'validtyPeriod';

    const SITE_IDENTIFIER = 'siteIdentifier';

    /** @var int */
    public $messageBoardStorageUid = 0;

    /** @var int */
    public $messageBoardPageUid = 0;

    /** @var int */
    public $validtyPeriod = 60;

    /** @var string */
    public $siteIdentifier = '';


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


    private function initialize()
    {
        $this->messageBoardStorageUid = intval($this->messageBoardStorageUid);

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
            ->executeQuery();
        while ($row = $s->fetchAssociative()) {
            $feUserStorageUids[] = $row['uid'];
        }

        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class, GeneralUtility::makeInstance(Context::class));


        $this->frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
        $this->frontendUserRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->frontendUserRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds($feUserStorageUids);
        $this->frontendUserRepository->setDefaultQuerySettings($querySettings);

        $this->messageRepository = GeneralUtility::makeInstance(MessageRepository::class);
        $this->messageRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->messageRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds([
            $this->messageBoardStorageUid
        ]);
        $this->messageRepository->setDefaultQuerySettings($querySettings);

        $this->frontendUserService = GeneralUtility::makeInstance(FrontendUserService::class, $this->frontendUserRepository);
    }

    private function validate()
    {
        $valid = true;

        $valid &= $this->pageRepository != null;
        $valid &= $this->messageRepository != null;
        $valid &= $this->frontendUserRepository != null;

        $valid &= $this->isPageUidValid($this->messageBoardStorageUid);
        $valid &= $this->isPageUidValid($this->messageBoardPageUid);
        $valid &= $this->isSiteIdentifierValid($this->siteIdentifier);

        return $valid;
    }

    private function isPageUidValid(int $id)
    {
        return $this->pageRepository->getPage($id) != null;
    }

    /**
     *
     * @return boolean
     */
    private function isSiteIdentifierValid(string $siteIdentifier): bool
    {
        try {
            GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($siteIdentifier);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function execute()
    {
        $this->initialize();

        if ($this->validate()) {

            // UPDATE tx_messageboard_domain_model_message SET expiry_date = ADDDATE(timestamp, 30);
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages')->createQueryBuilder();
            $queryBuilder->update('tx_messageboard_domain_model_message')
                ->set('expiry_date', 'ADDDATE(timestamp, ' . $this->validtyPeriod . ' )', false)
                ->where($queryBuilder->expr()->isNull('expiry_date'))
                ->executeStatement();
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
                        $this->sendInfoMail($user, $message);
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

    private function sendInfoMail(FrontendUser $user, Message $message)
    {
        if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {


            $fluidEmail = GeneralUtility::makeInstance(FluidEmail::class);
            $fluidEmail
                ->setRequest($this->createRequest($this->siteIdentifier))
                ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
                ->from(new Address(MailUtility::getSystemFromAddress(), LocalizationUtility::translate('task.messageBoardInformation.updateMail.senderName', MessageBoardInformationTask::EXTENSION_NAME)))
                ->subject(LocalizationUtility::translate('task.messageBoardInformation.infoMail.subject', MessageBoardInformationTask::EXTENSION_NAME))
                ->format(FluidEmail::FORMAT_BOTH) // send HTML and plaintext mail
                ->setTemplate('MessageBoardInfoMail')
                ->assign('user', $user)
                ->assign('message', $message)
                ->assign('pageUid', $this->messageBoardPageUid)
            ;
            GeneralUtility::makeInstance(MailerInterface::class)->send($fluidEmail);
        }
    }



    private function createRequest(string $siteIdentifier): RequestInterface
    {
        $serverRequestFactory = GeneralUtility::makeInstance(ServerRequestFactoryInterface::class);
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($siteIdentifier);
        $serverRequest = $serverRequestFactory->createServerRequest('GET', $site->getBase())
            ->withAttribute('applicationType', \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('site', $site)
            ->withAttribute('extbase', new \TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters());
        $request = GeneralUtility::makeInstance(Request::class, $serverRequest);
        //$GLOBALS['TYPO3_REQUEST'] = $request;
        if (!isset($GLOBALS['TYPO3_REQUEST'])) {
            $GLOBALS['TYPO3_REQUEST'] = $request;
        }
        return $request;
    }



    /**
     * This method returns the sleep duration as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation(): string
    {
        return 'Message board storage uid: ' . $this->messageBoardStorageUid
            . ' Message board page uid: ' . $this->messageBoardPageUid
            . ' Validity period: ' . $this->validtyPeriod
            . ' Site identifier: ' . $this->siteIdentifier;
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
            case MessageBoardInformationTask::MESSAGE_BOARD_PAGE_UID:
                return $this->messageBoardPageUid;
            case MessageBoardInformationTask::VALIDITY_PERIOD:
                return $this->validtyPeriod;
            case MessageBoardInformationTask::SITE_IDENTIFIER:
                return $this->siteIdentifier;
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
            case MessageBoardInformationTask::MESSAGE_BOARD_PAGE_UID:
                $this->messageBoardPageUid = $value;
                break;
            case MessageBoardInformationTask::VALIDITY_PERIOD:
                $this->validtyPeriod = $value;
                break;
            case MessageBoardInformationTask::SITE_IDENTIFIER:
                $this->siteIdentifier = $value;
                break;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }
}


