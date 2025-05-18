<?php
namespace Cylancer\CyMessageboard\Domain\Model;

/**
 * This file is part of the "message board" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 */
class Settings
{

    protected bool $infoMailWhenMessageBoardChanged = true;

    public function getInfoMailWhenMessageBoardChanged(): bool
    {
        return $this->infoMailWhenMessageBoardChanged;
    }

    public function setInfoMailWhenMessageBoardChanged(bool $b): void
    {
        $this->infoMailWhenMessageBoardChanged = $b;
    }
}   