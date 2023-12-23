<?php
namespace Cylancer\MessageBoard\Task;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

class MessageBoardInformationAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    const TRANSLATION_PREFIX = 'LLL:EXT:message_board/Resources/Private/Language/locallang.xlf:task.messageBoardInformation.';


    /**
     *
     * @param array $taskInfo
     * @param MessageBoardInformationTask|null $task
     * @param SchedulerModuleController $schedulerModule
     * @param string $key
     * @param array $additionalFields
     * @return void
     */
    private function initHintText(array &$additionalFields)
    {
        // Write the code for the field
        $fieldID = 'task_hint';
        $fieldCode = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('task.messageBoardInformation.hint.text', MessageBoardInformationTask::EXTENSION_NAME);
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . '.title',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }
    /**
     *
     * @param array $taskInfo
     * @param MessageBoardInformationTask|null $task
     * @param SchedulerModuleController $schedulerModule
     * @param string $key
     * @param array $additionalFields
     * @return void
     */
    private function initIntegerAddtionalField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule, string $key, array &$additionalFields)
    {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();

        // Initialize extra field value
        if (empty($taskInfo[$key])) {
            if ($currentSchedulerModuleAction->equals(Action::ADD)) {
                // In case of new task and if field is empty, set default sleep time
                $taskInfo[$key] = 0;
            } elseif ($currentSchedulerModuleAction->equals(Action::EDIT)) {
                // In case of edit, set to internal value if no data was submitted already
                $taskInfo[$key] = $task->get($key);
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo[$key] = 0;
            }
        }

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

    /**
     *
     * @param array $taskInfo
     * @param MessageBoardInformationTask|null $task
     * @param SchedulerModuleController $schedulerModule
     * @param string $key
     * @param array $additionalFields
     * @return void
     */
    private function initUrlAddtionalField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule, string $key, array &$additionalFields)
    {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();

        // Initialize extra field value
        if (empty($taskInfo[$key])) {
            if ($currentSchedulerModuleAction->equals(Action::ADD)) {
                // In case of new task and if field is empty, set default sleep time
                $taskInfo[$key] = 'https://';
            } elseif ($currentSchedulerModuleAction->equals(Action::EDIT)) {
                // In case of edit, set to internal value if no data was submitted already
                $taskInfo[$key] = $task->get($key);
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo[$key] = 'https://';
            }
        }

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



    /**
     * This method is used to define new fields for adding or editing a task
     * In this case, it adds a sleep time field
     *
     * @param array $taskInfo
     *            Reference to the array containing the info used in the add/edit form
     * @param MessageBoardInformationTask|null $task
     *            When editing, reference to the current task. NULL when adding.
     * @param SchedulerModuleController $schedulerModule
     *            Reference to the calling object (Scheduler's BE module)
     * @return array Array containing all the information pertaining to the additional fields
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $additionalFields = [];
        $this->initHintText($additionalFields);
        $this->initIntegerAddtionalField($taskInfo, $task, $schedulerModule, MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID, $additionalFields);
        $this->initUrlAddtionalField($taskInfo, $task, $schedulerModule, MessageBoardInformationTask::MESSAGE_BOARD_URL, $additionalFields);

        // debug($additionalFields);
        return $additionalFields;
    }

    /**
     *
     * @param array $submittedData
     * @param SchedulerModuleController $schedulerModule
     * @param string $key
     * @return boolean
     */
    private function validatePageAdditionalField(array &$submittedData, SchedulerModuleController $schedulerModule, string $key)
    {
        $result = true;
        if (!$this->validatePage($submittedData[$key])) {
            $this->addMessage($this->getLanguageService()
                ->sL(MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalidPage.' . $key), FlashMessage::ERROR);
            $result = false;
        }

        return $result;
    }

    /**
     *
     * @param array $submittedData
     * @param SchedulerModuleController $schedulerModule
     * @param string $key
     * @return boolean
     */
    private function validateUrlAdditionalField(array &$submittedData, SchedulerModuleController $schedulerModule, string $key)
    {
        $url = trim($submittedData[$key]);
        if (strlen($url) == 0) {
            return true;
        }
        if (!(is_string($url) && strlen($url) > 5 && filter_var($url, FILTER_VALIDATE_URL))) {
            $this->addMessage(str_replace('%1', $submittedData[$key], $this->getLanguageService()
                ->sL(MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalidUrl.' . $key)), FlashMessage::ERROR);
            return false;
        }
        return true;
    }

    /**
     *
     * @param array $submittedData
     * @param SchedulerModuleController $schedulerModule
     * @param string $key
     * @return boolean
     */
    private function validateIntegerAdditionalField(array &$submittedData, SchedulerModuleController $schedulerModule, string $key)
    {
        $result = true;

        $submittedData[$key] = (int) $submittedData[$key];
        if ($submittedData[$key] < 0) {
            $this->addMessage($this->getLanguageService()
                ->sL(MessageBoardInformationAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalid.' . $key), FlashMessage::ERROR);
            $result = false;
        }

        return $result;
    }



    private function validatePage($pid)
    {
        $pageRepository = $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class, GeneralUtility::makeInstance(Context::class));
        return trim($pid) == strval(intval($pid)) && $pageRepository->getPage($pid) != null;
    }

    /**
     * This method checks any additional data that is relevant to the specific task
     * If the task class is not relevant, the method is expected to return TRUE
     *
     * @param array $submittedData
     *            Reference to the array containing the data submitted by the user
     * @param SchedulerModuleController $schedulerModule
     *            Reference to the calling object (Scheduler's BE module)
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $result = true;
        $result &= $this->validatePageAdditionalField($submittedData, $schedulerModule, MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID);
        $result &= $this->validateUrlAdditionalField($submittedData, $schedulerModule, MessageBoardInformationTask::MESSAGE_BOARD_URL);
        return $result;
    }

    /**
     *
     * @param array $submittedData
     * @param AbstractTask $task
     * @param string $key
     * @return void
     */
    public function saveAdditionalField(array $submittedData, AbstractTask $task, string $key)
    {
        /**
         * @var MessageBoardInformationTask $task
         */
        $task->set($key, $submittedData[$key]);
    }

    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches
     *
     * @param array $submittedData
     *            Array containing the data submitted by the user
     * @param MessageBoardInformationTask $task
     *            Reference to the current task object
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $this->saveAdditionalField($submittedData, $task, MessageBoardInformationTask::MESSAGE_BOARD_STORAGE_UID);
        $this->saveAdditionalField($submittedData, $task, MessageBoardInformationTask::MESSAGE_BOARD_URL);
    }

    /**
     *
     * @return LanguageService|null
     */
    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }
}
