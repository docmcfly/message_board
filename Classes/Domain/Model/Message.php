<?php
namespace Cylancer\CyMessageboard\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This file is part of the "Message board" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 */
class Message extends AbstractEntity
{

    protected ?string $text = '';

    protected ?FrontendUser $user = null;

    protected bool $changed = true;

    protected ?\DateTime $timestamp = null;

    protected ?\DateTime $expiryDate = null;

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setUser(?FrontendUser $user): void
    {
        $this->user = $user;
    }

    public function getUser(): ?FrontendUser
    {
        return $this->user;
    }

    public function getTimestamp(): ?\DateTime
    {
        return $this->timestamp;
    }

    public function setTimestamp(?\DateTime $timestamp): void
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

   public function setExpiryDate(?\DateTime $expiryDate): void
    {
        $this->expiryDate = $expiryDate;
    }
    
    public function getChanged(): bool
    {
        return $this->changed;
    }

    public function setChanged(bool $changed): void
    {
        $this->changed = $changed;
    }
}