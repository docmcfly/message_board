<?php
namespace Cylancer\CyMessageboard\Task;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

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

class MessageBoardInformationAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    private const TRANSLATION_PREFIX = 'LLL:EXT:cy_messageboard/Resources/Private/Language/locallang.xlf:task.messageBoardInformation.';

    private function initHintText(array &$additionalFields)
    {
        // Write the code for the field
        $fieldID = 'task_hint';
        $fieldCode = LocalizationUtility::translate('task.messageBoardInformation.hint.text', MessageBoardInformationTask::EXTENSION_NAME);
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . '.title',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    private function getDefault(string $key): string|int
    {
        switch ($key) {
            case MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID:
            case MessageBoardInformationTask::MESSAGE_BOARD_PAGE_UID:
            case MessageBoardInformationTask::VALIDITY_PERIOD:
                return 0;
            case MessageBoardInformationTask::SUBJECT:
                return LocalizationUtility::translate(
                    MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . 'subject.default',
                    MessageBoardInformationTask::EXTENSION_NAME
                );
            case MessageBoardInformationTask::SENDER_NAME:
                return MailUtility::getSystemFromName();
            default:
                return '';
        }
    }

    private function setCurrentKey(array &$taskInfo, ?MessageBoardInformationTask $task, string $key): void
    {
        if (empty($taskInfo[$key])) {
            $taskInfo[$key] = $task != null ? $task->get($key) : $this->getDefault($key);
        }
    }


    private function initIntegerAddtionalField(array &$taskInfo, $task, string $key, array &$additionalFields): void
    {
        $this->setCurrentKey($taskInfo, $task, $key);

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="number" min="0" max="99999" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="' . $taskInfo[$key] . '" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    private function initUrlAddtionalField(array &$taskInfo, $task, string $key, array &$additionalFields)
    {
        $this->setCurrentKey($taskInfo, $task, $key);

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="url" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="' . $taskInfo[$key] . '" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    private function initStringAddtionalField(array &$taskInfo, $task, string $key, array &$additionalFields)
    {
        $this->setCurrentKey($taskInfo, $task, $key);

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="text" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="' . $taskInfo[$key] . '" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }


    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $additionalFields = [];
        $this->initHintText($additionalFields);
        $this->initIntegerAddtionalField($taskInfo, $task, MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID, $additionalFields);
        $this->initIntegerAddtionalField($taskInfo, $task, MessageBoardInformationTask::MESSAGE_BOARD_PAGE_UID, $additionalFields);
        $this->initIntegerAddtionalField($taskInfo, $task, MessageBoardInformationTask::VALIDITY_PERIOD, $additionalFields);
        $this->initStringAddtionalField($taskInfo, $task, MessageBoardInformationTask::SENDER_NAME, $additionalFields);
        $this->initStringAddtionalField($taskInfo, $task, MessageBoardInformationTask::SUBJECT, $additionalFields);
        $this->initStringAddtionalField($taskInfo, $task, MessageBoardInformationTask::SITE_IDENTIFIER, $additionalFields);

        // debug($additionalFields);
        return $additionalFields;
    }

    private function validatePageAdditionalField(array &$submittedData, string $key): bool
    {
        if (!$this->validatePage($submittedData[$key])) {
            $this->addMessage($this->getLanguageService()
                ->sL(MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalidPage.' . $key), ContextualFeedbackSeverity::ERROR);
            return false;
        }

        return true;
    }

    private function validateUrlAdditionalField(array &$submittedData, string $key): bool
    {
        $url = trim($submittedData[$key]);
        if (strlen($url) == 0) {
            return true;
        }
        if (!(is_string($url) && strlen($url) > 5 && filter_var($url, FILTER_VALIDATE_URL))) {
            $this->addMessage(str_replace('%1', $submittedData[$key], $this->getLanguageService()
                ->sL(MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalidUrl.' . $key)), ContextualFeedbackSeverity::ERROR);
            return false;
        }
        return true;
    }

    private function validateIntegerAdditionalField(array &$submittedData, string $key): bool
    {

        $submittedData[$key] = (int) $submittedData[$key];
        if ($submittedData[$key] < 0) {
            $this->addMessage($this->getLanguageService()
                ->sL(MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalid.' . $key), ContextualFeedbackSeverity::ERROR);
            return false;
        }

        return true;
    }

    private function validateSitedField(array &$submittedData, string $key)
    {
        try {
            GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($submittedData[$key]);
            return true;
        } catch (\Exception $e) {
            $this->addMessage($this->getLanguageService()
                ->sL(MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.siteNotFound.' . $key), ContextualFeedbackSeverity::ERROR);
            return false;
        }
    }


    private function validateRequiredField(array &$submittedData, string $key): bool
    {
        if (empty($submittedData[$key])) {
            $this->addMessage($this->getLanguageService()
                ->sL(MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.required.' . $key), ContextualFeedbackSeverity::ERROR);
            return false;
        }
        return true;
    }

    private function validatePage($pid)
    {
        $pageRepository = $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class, GeneralUtility::makeInstance(Context::class));
        return trim($pid) == strval(intval($pid)) && $pageRepository->getPage($pid) != null;
    }

    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $result = true;
        $result &= $this->validatePageAdditionalField($submittedData, MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID);
        $result &= $this->validatePageAdditionalField($submittedData, MessageBoardInformationTask::MESSAGE_BOARD_PAGE_UID);
        $result &= $this->validateIntegerAdditionalField($submittedData, MessageBoardInformationTask::VALIDITY_PERIOD);
        $result &= $this->validateSitedField($submittedData, MessageBoardInformationTask::SITE_IDENTIFIER);
        $result &= $this->validateRequiredField($submittedData, MessageBoardInformationTask::SUBJECT);
        return $result;
    }

    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->set($submittedData);
    }

    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }
}
