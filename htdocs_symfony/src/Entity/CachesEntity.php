<?php

namespace Oc\Entity;

use Oc\Repository\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @ORM\Entity(repositoryClass="Oc\Repository\CachesRepository")
 */
class CachesEntity extends AbstractEntity //implements UserInterface
{
    /**
     * @var int
     */
    public $cache_id;

//    /**
//     * @var int
//     */
//    public $id;
//
//    /**
//     * @var string
//     */
//    public $username;
//
//    /**
//     * @var string
//     */
//    public $password;
//
//    /**
//     * @var string
//     */
//    public $email;
//
//    /**
//     * @var float
//     */
//    public $latitude;
//
//    /**
//     * @var float
//     */
//    public $longitude;
//
//    /**
//     * @var bool
//     */
//    public $isActive;
//
//    /**
//     * @var string
//     */
//    public $firstname;
//
//    /**
//     * @var string
//     */
//    public $lastname;
//
//    /**
//     * @var string
//     */
//    public $country;
//
//    /**
//     * @var string
//     */
//    public $language;
//
//    /**
//     * @var array
//     */
//    public $roles;

    /**
     * Checks if the entity is new.
     */
    public function isNew(): bool
    {
        return $this->id === null;
    }

//    /**
//     * Checks if the entity is new.
//     */
//    public function isNew(): bool
//    {
//        return $this->id === null;
//    }
//
//    public function getRoles(): array
//    {
//        return $this->roles;
//    }
//
//    public function getPassword(): ?string
//    {
//        return $this->password;
//    }
//
//    public function getSalt(): string
//    {
//        return '';
//    }
//
//    public function getUsername(): string
//    {
//        return $this->username;
//    }
//
//    public function eraseCredentials(): void
//    {
//    }
}
