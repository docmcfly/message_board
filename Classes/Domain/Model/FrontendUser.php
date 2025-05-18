<?php
namespace Cylancer\CyMessageboard\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 *
 * This file is part of the "message board" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */
class FrontendUser extends AbstractEntity
{

    protected ?string $name = '';

    protected  ?string $firstName = '';

    protected  ?string $lastName = '';


    protected  ?string $username = '';

    protected  ?string $email = '';

    protected bool $infoMailWhenMessageBoardChanged = true;


    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setFirstName(?string$firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getFirstName():?string
    {
        return $this->firstName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getLastName():?string
    {
        return $this->lastName;
    }     public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getInfoMailWhenMessageBoardChanged(): bool
    {
        return $this->infoMailWhenMessageBoardChanged;
    }

    public function setInfoMailWhenMessageBoardChanged(bool $b): void
    {
        $this->infoMailWhenMessageBoardChanged = $b;
    }

	public function getUsername():?string {
		return $this->username;
	}

    public function setUsername(?string $username): self {
		$this->username = $username;
		return $this;
	}
}