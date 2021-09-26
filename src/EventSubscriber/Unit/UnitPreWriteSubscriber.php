<?php

namespace App\EventSubscriber\Unit;

use App\Entity\Unit;
use App\Exception\AccessDeniedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Core\EventListener\EventPriorities;

class UnitPreWriteSubscriber implements EventSubscriberInterface
{
    /**
     * UnitPreWriteSubscriber constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param ViewEvent $event
     * @throws AccessDeniedException
     */
    public function onKernelView(ViewEvent $event)
    {
        $unit = $event->getControllerResult();
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (!($unit instanceof Unit)) {
            return;
        }

        if ('api_units_put_item' == $route || 'api_units_delete_item' == $route) {
            if ($unit->getReadOnly()) {
                throw new AccessDeniedException("No es posible borrar o modificar esta unidad, es de solo lectura.");
            }
        }
        return;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE]
        ];
    }
}