<?php
namespace Cylancer\MessageBoard\Domain\Model;

/**
 * This file is part of the "message board" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin <service@cylancer.net>
 */
class Settings
{

    /**
     *
     * @var boolean
     */
    protected $infoMailWhenMessageBoardChanged = true;

    /**
     *
     * @return boolean
     */
    public function getInfoMailWhenMessageBoardChanged()
    {
        return $this->infoMailWhenMessageBoardChanged;
    }

    /**
     *
     * @param boolean $b
     */
    public function setInfoMailWhenMessageBoardChanged(bool $b)
    {
        $this->infoMailWhenMessageBoardChanged = $b;
    }
}   