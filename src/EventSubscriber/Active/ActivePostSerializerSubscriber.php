<?php
/**
 * Created by PhpStorm.
 * User: SUSAN MEDINA
 * Date: 04/06/2019
 * Time: 12:19 PM
 */

namespace App\EventSubscriber\Active;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Repository\ActiveTypeRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ActivePostSerializerSubscriber implements EventSubscriberInterface
{


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
            'api_actives_get_collection'
        );

        if (!in_array($route, $routes))
            return;

        if ('api_actives_get_collection' === $route ) {
            $content = json_decode($event->getControllerResult());
            $content['page'] = $request->attributes->get('data')->getCurrentPage();
            $content['itemsPerPage'] = $request->attributes->get('data')->getItemsPerPage();
            $content['count'] = $request->attributes->get('data')->count();

            $content = json_encode($content);
            $event->setControllerResult($content);
        }
    }

}