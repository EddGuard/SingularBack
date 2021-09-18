<?php
/**
 * Created by PhpStorm.
 * User: SUSAN MEDINA
 * Date: 04/06/2019
 * Time: 12:19 PM
 */

namespace App\EventSubscriber\ActiveRecord;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Services\ActiveRecordService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ActiveRecordPostSerializerSubscriber implements EventSubscriberInterface
{
    private ActiveRecordService $activeRecordService;

    public function __construct(ActiveRecordService $activeRecordService)
    {
        $this->activeRecordService = $activeRecordService;
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
            'api_active_records_get_collection',
            'api_active_records_get_item',
        );

        if (!in_array($route, $routes))
            return;


        $recordResult = $event->getControllerResult();
        $recordResult = json_decode($recordResult, true);

        if ('api_active_records_get_collection' === $route ) {
            foreach ($recordResult as &$record) {
                $record = $this->activeRecordService->formatNormalize($record);
            }
        } else {
            $recordResult = $this->activeRecordService->formatNormalize($recordResult);
        }
        $recordResult = json_encode($recordResult);
        $event->setControllerResult($recordResult);
    }
    
}