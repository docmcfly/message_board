<?php
namespace Cylancer\MessageBoard\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This file is part of the "Message board" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024 C. Gogolin <service@cylancer.net>
 */
class Message extends AbstractEntity
{

    /**
     *
     * @var string
     */
    protected $text = '';

    /**
     *
     * @var FrontendUser
     */
    protected $user = null;

    /**
     *
     * @var bool
     */
    protected $changed = true;

    /**
     *
     * @var \DateTime $timestamp
     */
    protected $timestamp = null;

    /**
     *
     * @var \DateTime $expiryDate
     */
    protected $expiryDate = null;

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     *
     * @param FrontendUser $user
     * @return void
     */
    public function setUser(FrontendUser $user): void
    {
        $this->user = $user;
    }

    /**
     *
     * @return FrontendUser
     */
    public function getUser(): ?FrontendUser
    {
        return $this->user;
    }

    /**
     *
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     *
     * @param \DateTime $timestamp
     */
    public function setTimestamp(\DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return ?\DateTime
     */
    public function getExpiryDate() : ?\DateTime
    {
        return $this->expiryDate;
    }

   public function setExpiryDate(\DateTime $expiryDate): void
    {
        $this->expiryDate = $expiryDate;
    }
    
    /**
     *
     * @return boolean
     */
    public function getChanged(): bool
    {
        return $this->changed;
    }

    /**
     *
     * @param boolean $changed
     */
    public function setChanged($changed): void
    {
        $this->changed = $changed;
    }
}