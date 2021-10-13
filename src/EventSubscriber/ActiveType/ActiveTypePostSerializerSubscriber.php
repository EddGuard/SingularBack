<?php
/**
 * Created by PhpStorm.
 * User: SUSAN MEDINA
 * Date: 04/06/2019
 * Time: 12:19 PM
 */

namespace App\EventSubscriber\ActiveType;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Repository\ActiveTypeRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ActiveTypePostSerializerSubscriber implements EventSubscriberInterface
{

    /**
     * @var ActiveTypeRepository
     */
    private ActiveTypeRepository $activeTypeRepository;

    public function __construct(ActiveTypeRepository $activeTypeRepository)
    {
        $this->activeTypeRepository = $activeTypeRepository;
    }


    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::POST_SERIALIZE]
        ];
    }

    /**
     * @param ViewEvent $event
     * @throws \Exception
     */
    public function onKernelView(ViewEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        $routes = array(
            'api_active_types_get_collection',
            'api_active_types_get_item',
        );

        if (!in_array($route, $routes))
            return;


        $typeResult = $event->getControllerResult();
        $typeResult = json_decode($typeResult, true);

        if ('api_active_types_get_collection' === $route ) {
            foreach ($typeResult as &$type) {
                $type["activesCount"] = $this->activeTypeRepository->getActivesCountByTypeId($type["id"]);
            }
        } else {
            $typeResult["activesCount"] = $this->activeTypeRepository->getActivesCountByTypeId($typeResult["id"]);;
        }
        $typeResult = json_encode($typeResult);
        $event->setControllerResult($typeResult);
    }

}