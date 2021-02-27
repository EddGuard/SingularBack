<?php


namespace App\EventSubscriber\User;

use App\Exception\NotFoundException;
use App\Exception\UserException;
use App\Repository\RolesRepository;
use App\Repository\UserRepository;
use App\Exception\InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Services\UserService;
use App\Entity\User;
use App\Entity\Roles;

final class UserPostValidateSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;
    private $authorizationChecker;
    private $validator;
    private $userService;
    private $vendorService;
    private $roleRepository;
    private $vendorRepository;
    private $locationRepository;
    private $companyRepository;
    private $categoryRepository;
    private $userRepository;
    private $workerBudgetRepository;
    private $translator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $checker,
        ValidatorInterface $validator,
        UserService $userService,
        RolesRepository $roleRepository,
        UserRepository $userRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $checker;
        $this->validator = $validator;
        $this->userService = $userService;
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return [
            KernelEvents::VIEW => ['prepareUserData', EventPriorities::POST_VALIDATE]
        ];
    }

    /**
     * @param ViewEvent $event
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws UserException
     */
    public function prepareUserData(ViewEvent $event) {

        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $request = $event->getRequest();

        if (!($user instanceof User) ||
            (Request::METHOD_POST !== $method && Request::METHOD_PUT !== $method))
            return;

        $content = $request->getContent();
        $data = json_decode($content, true);

        if (isset($data['roles'])) {
            $rolesObject = [];
            $rolesName = [];
            foreach ($data['roles'] as $role) {
                $entityId = null;
                if (is_string($role)) {
                    $entityId = $role;
                    $role = $this->roleRepository->findOneBy(['name' => $role]);
                } else if(is_array($role) && isset($role['id'])) {
                    $entityId = $role['id'];
                    $role = $this->roleRepository->find($role['id']);
                }

                if (!$role instanceof Roles) {
                    throw new NotFoundException(
                        "Role not found"
                    );
                }

                $rolesObject[] = $role;
                $rolesName[] = $role->getName();
                $user->addGroup($role);
            }
        } else {
            if (Request::METHOD_POST === $method) {
                throw new InvalidArgumentException("Bad request");
            }
        }
    }
}
