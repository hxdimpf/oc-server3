<?php

namespace Oc\Entity;

use DateTime;
use Oc\Repository\AbstractEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserEntity
 *
 * @package Oc\Entity
 */
class UserEntity extends AbstractEntity implements UserInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var DateTime
     */
    public $lastLogin;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $email;

    /**
     * @var int
     */
    public $emailProblems;

    /**
     * @var float
     */
    public $latitude;

    /**
     * @var float
     */
    public $longitude;

    /**
     * @var bool
     */
    public $isActive;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $country;

    /**
     * @var string
     */
    public $language;

    /**
     * @var array
     */
    public $roles;

    /**
     * Checks if the entity is new.
     */
    public function isNew(): bool
    {
        return $this->id === null;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSalt(): string
    {
        return '';
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
    }
}
