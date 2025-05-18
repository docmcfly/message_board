<?php
namespace Cylancer\CyMessageboard\Task;

use Cylancer\CyMessageboard\Domain\Model\Message;
use Cylancer\CyMessageboard\Domain\Repository\FrontendUserRepository;
use Cylancer\CyMessageboard\Service\FrontendUserService;
use Cylancer\CyMessageboard\Domain\Model\FrontendUser;
use Cylancer\CyMessageboard\Domain\Repository\MessageRepository;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Symfony\Component\Mime\Address;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Core\Http\ServerRequest;

/**
 *
 * This file is part of the "Messageboard" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *       
 */

class MessageBoardInformationTask extends AbstractTask
{


    public const MESSAGE_BOARD_STORAGE_UID = 'messageBoardStorageUid';
    public const MESSAGE_BOARD_PAGE_UID = 'messageBoardPageUid';
    public const VALIDITY_PERIOD = 'validtyPeriod';
    public const SENDER_NAME = 'senderName';
    public const SUBJECT = 'subject';
    public const SITE_IDENTIFIER = 'siteIdentifier';

    public int $messageBoardStorageUid = 0; 
    public int $messageBoardPageUid = 0; 
    public int $validtyPeriod = 60; 
    public string $senderName = ''; 
    public string $subject = ''; 
    public string $siteIdentifier = ''; 

    // ------------------------------------------------------
    // debug switch
    private const DISABLE_PERSISTENCE_MANAGER = false;

    public const EXTENSION_NAME = 'MessageBoard';

    // ------------------------------------------------------

    private ?FrontendUserService $frontendUserService = null;

    private ?FrontendUserRepository $frontendUserRepository = null;

    private ?MessageRepository $messageRepository = null;

    private ?PageRepository $pageRepository = null;

    private ?PersistenceManager $persistenceManager = null; 
    

    private function initialize(): void
    {
        $this->messageBoardStorageUid = intval($this->messageBoardStorageUid);

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        $feUserStorageUids = [];
        
        /** @var QueryBuilder $qb */
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

    private function validate(): int
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

    private function isPageUidValid(int $id): bool
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

    public function execute(): bool
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

    private function sendInfoMail(FrontendUser $user, Message $message): void
    {
        if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {

            $fluidEmail = GeneralUtility::makeInstance(FluidEmail::class);
            $fluidEmail
                ->setRequest($this->createRequest($this->siteIdentifier))
                ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
                ->from(new Address(MailUtility::getSystemFromAddress(), $this->senderName))
                ->subject($this->subject)
                ->format(FluidEmail::FORMAT_BOTH) // send HTML and plaintext mail
                ->setTemplate('MessageBoardInfoMail')
                ->assign('user', $user)
                ->assign('message', $message)
                ->assign('pageUid', $this->messageBoardPageUid)
            ;
            GeneralUtility::makeInstance(MailerInterface::class)->send($fluidEmail);
        }
    }
    private function createRequest(string $siteIdentifier): ServerRequest
    {
        $serverRequestFactory = GeneralUtility::makeInstance(ServerRequestFactoryInterface::class);
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($siteIdentifier);
        $serverRequest = $serverRequestFactory->createServerRequest('GET', $site->getBase())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('site', $site)
            ->withAttribute('extbase', GeneralUtility::makeInstance(ExtbaseRequestParameters::class))
        ;
        return $serverRequest;
    } 

    public function getAdditionalInformation(): string
    {
        return 'Message board storage uid: ' . $this->messageBoardStorageUid
            . ' Message board page uid: ' . $this->messageBoardPageUid
            . ' Validity period: ' . $this->validtyPeriod
            . ' Subject: ' . $this->subject
            . ' Site identifier: ' . $this->siteIdentifier;
    }

    public function get(string $key): int|string
    {
        switch ($key) {
            case MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID:
                return intval($this->messageBoardStorageUid);
            case MessageBoardInformationTask::MESSAGE_BOARD_PAGE_UID:
                return intval($this->messageBoardPageUid);
            case MessageBoardInformationTask::VALIDITY_PERIOD:
                return intval($this->validtyPeriod);
            case MessageBoardInformationTask::SITE_IDENTIFIER:
                return $this->siteIdentifier;
            case MessageBoardInformationTask::SENDER_NAME:
                return $this->senderName;
            case MessageBoardInformationTask::SUBJECT:
                return $this->subject;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }

    public function set(array $data): void
    {

        foreach ([ // 
            MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID,// 
            MessageBoardInformationTask::MESSAGE_BOARD_PAGE_UID, // 
            MessageBoardInformationTask::VALIDITY_PERIOD, // 
            MessageBoardInformationTask::SENDER_NAME, // 
            MessageBoardInformationTask::SUBJECT, // 
            MessageBoardInformationTask::SITE_IDENTIFIER// 
        ] as $key) {
            $value = $data[$key];
            switch ($key) {
                case MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID:
                    $this->messageBoardStorageUid = intval($value);
                    break;
                case MessageBoardInformationTask::MESSAGE_BOARD_PAGE_UID:
                    $this->messageBoardPageUid = intval($value);
                    break;
                case MessageBoardInformationTask::VALIDITY_PERIOD:
                    $this->validtyPeriod = intval($value);
                    break;
                case MessageBoardInformationTask::SITE_IDENTIFIER:
                    $this->siteIdentifier = $value;
                    break;
                case MessageBoardInformationTask::SUBJECT:
                    $this->subject = $value;
                    break;
                case MessageBoardInformationTask::SENDER_NAME:
                    $this->senderName = $value;
                    break;
                default:
                    throw new \Exception("Unknown key: $key");
            }
        }
    }


    /**
     * 
     * @deprecated remove if all instances with the correct types are saved.
     * @return bool
     */
    public function save(): bool
    {
        $this->messageBoardStorageUid = intval($this->messageBoardStorageUid);
        $this->messageBoardPageUid = intval($this->messageBoardPageUid);
        $this->validtyPeriod = intval($this->validtyPeriod);
        return parent::save();

    }
}


