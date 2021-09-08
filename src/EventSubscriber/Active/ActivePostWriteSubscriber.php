<?php

namespace App\EventSubscriber\Active;

use App\Entity\Active;
use App\Entity\AttributeValue;
use App\Exception\GeneralException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\ORM\EntityManagerInterface;

class ActivePostWriteSubscriber implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param ViewEvent $event
     * @throws GeneralException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function onKernelView(ViewEvent $event)
    {
        $active = $event->getControllerResult();
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (!($active instanceof Active)) {
            return;
        }
        if ('api_actives_post_collection' == $route) {

            $this->entityManager->getConnection()->beginTransaction();
            try {
                $type = $active->getActiveType();
                
                if(empty($active->getAttributeValues())) {
                    foreach ($type->getBasicAttributes() as $basicAttribute):
                        $attributeValue = new AttributeValue();
                        $attributeValue->setName($basicAttribute->getName());
                        $attributeValue->setValue($basicAttribute->getValue());
                        $this->entityManager->persist($attributeValue);
                        $active->addAttributeValue($attributeValue);
                        $this->entityManager->persist($active);
                    endforeach;
                    foreach ($type->getCustomAttributes() as $customAttribute):
                        $attributeValue = new AttributeValue();
                        $attributeValue->setName($customAttribute->getName());
                        $attributeValue->setValue($customAttribute->getValue());
                        $this->entityManager->persist($attributeValue);
                        $active->addAttributeValue($attributeValue);
                        $this->entityManager->persist($active);
                    endforeach;
                }

                $this->entityManager->flush();
                $this->entityManager->getConnection()->commit();
            } catch (\Exception $exception) {
                $this->entityManager->getConnection()->rollback();
                $this->entityManager->remove($active);
                //$this->entityManager->clear();

                throw new GeneralException($exception->getMessage());
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