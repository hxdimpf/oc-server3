<?php

declare(strict_types=1);

namespace Oc\Security;

use Doctrine\DBAL\Driver\Exception;
use Oc\Entity\UserEntity;
use Oc\Repository\Exception\RecordNotFoundException;
use Oc\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 *
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param $username
     *
     * @return UserInterface
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function loadUserByUsername($username)
    : UserInterface {
        try {
            return $this->userRepository->fetchOneByUsername($username);
        } catch (RecordNotFoundException $e) {
            throw new UserNotFoundException('User by username "' . $username . '" not found!', 0, $e);
        }
    }

    /**
     * @param UserInterface $user
     *
     * @return UserInterface
     * @throws RecordNotFoundException
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function refreshUser(UserInterface $user)
    : UserInterface {
        if (!$user instanceof UserEntity) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        return $this->userRepository->fetchOneByUsername($user->getUsername());
    }

    /**
     * @param $class
     *
     * @return bool
     */
    public function supportsClass($class)
    : bool {
        return UserEntity::class === $class || is_subclass_of($class, UserEntity::class);
    }
}
