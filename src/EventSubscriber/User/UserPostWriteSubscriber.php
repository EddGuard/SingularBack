<?php

namespace App\EventSubscriber\User;

use App\Entity\User;
use App\Exception\GeneralException;
use App\Exception\NotFoundException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\UserService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserPostWriteSubscriber implements EventSubscriberInterface
{
    private $userService;
    private $entityManager;
    private $tokenStorage;
    private $authorizationChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $checker
    )
    {
        $this->entityManager = $entityManager;
        $this->userService = $userService;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $checker;
    }

    /**
     * @param ViewEvent $event
     * @throws GeneralException
     * @throws NotFoundException
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\ORMException
     */
    public function onKernelView(ViewEvent $event)
    {
        $user = $event->getControllerResult();
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (!($user instanceof User)) {
            return;
        }
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $userCurrent = $token->getUser();

            if (!($userCurrent instanceof User)) {
                throw new NotFoundException($userCurrent->getUsername());
            }

            if (!$this->entityManager->isOpen()) {
                $this->entityManager = EntityManager::create(
                    $this->entityManager->getConnection(),
                    $this->entityManager->getConfiguration()
                );
            }

            if (false == $this->entityManager->getConnection()->ping()) {
                $this->entityManager->getConnection()->close();
                $this->entityManager->getConnection()->connect();
            }

            if ('api_users_post_collection' == $route || 'api_users_put_item' == $route) {

                $this->entityManager->getConnection()->beginTransaction();
                try {
                    $plainPassword = $user->getPlainPassword();
                    if (!is_null($plainPassword)) {
                        $this->userService->encodePassword($user, false, false);
                    }
                    $this->entityManager->persist($user);

                    $this->entityManager->flush();
                    $this->entityManager->getConnection()->commit();
                } catch (\Exception $exception) {
                    $this->entityManager->getConnection()->rollback();
                    $this->entityManager->remove($user);
                    //$this->entityManager->clear();

                    throw new GeneralException($exception->getMessage());
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::POST_WRITE]
        ];
    }
}