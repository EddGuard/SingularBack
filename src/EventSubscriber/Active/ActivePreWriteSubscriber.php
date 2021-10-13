<?php

namespace App\EventSubscriber\Active;

use App\Entity\Active;
use App\Entity\ActiveRecord;
use App\Entity\ActiveType;
use App\Entity\AttributeValue;
use App\Entity\Unit;
use App\Exception\GeneralException;
use App\Repository\AttributeValueRepository;
use App\Repository\UnitRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\ORM\EntityManagerInterface;

class ActivePreWriteSubscriber implements EventSubscriberInterface
{
    private $entityManager;
    /**
     * @var AttributeValueRepository
     */
    private AttributeValueRepository $attributeValueRepository;
    /**
     * @var UnitRepository
     */
    private UnitRepository $unitRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        AttributeValueRepository $attributeValueRepository, UnitRepository $unitRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->attributeValueRepository = $attributeValueRepository;
        $this->unitRepository = $unitRepository;
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
        $content = json_decode($request->getContent());


        if (!($active instanceof Active)) {
            return;
        }
        if ('api_actives_post_collection' == $route || 'api_actives_put_item' == $route):
            foreach ($content as $key => $items):
                if ($key == 'basicAttributes') {
                    foreach ($items as $item) {
                        $attributeVal = null;
                        if (property_exists($item, 'id')) {
                            $attributeVal = $this->attributeValueRepository->findOneBy(["id" => $item->id, "activeBasics" => $active]);
                        }
                        if (empty($attributeVal)) {
                            $attributeVal = new AttributeValue();
                        }
                        $attributeVal->setName($item->name);
                        $attributeVal->setValue($item->value);
                        if (property_exists($item, "unit") && !empty($item->unit)) {
                            $unit = null;
                            if (property_exists($item->unit, "id")) {
                                $unit = $this->unitRepository->find($item->unit->id);
                            }
                            if (empty($unit)) {
                                $unit = new Unit();
                            }
                            $unit->setName($item->unit->name);
                            if (property_exists($item->unit, "readOnly")) {
                                $unit->setReadOnly($item->unit->readOnly);
                            } else {
                                $unit->setReadOnly(false);
                            }
                            $this->entityManager->persist($unit);
                            $attributeVal->setUnit($unit);
                        }
                        $this->entityManager->persist($attributeVal);
                        $active->addBasicAttributes($attributeVal);
                    }
                }
                elseif ($key == 'customAttributes') {
                    $active->removeAllCustomAttributes();
                    foreach ($items as $item):
                        $attributeVal = null;
                        if (property_exists($item, 'id')) {
                            $attributeVal = $this->attributeValueRepository->find($item->id);
                        }
                        if (empty($attributeVal)) {
                            $attributeVal = new AttributeValue();
                        }
                        $attributeVal->setName($item->name);
                        $attributeVal->setValue($item->value);
                        if (property_exists($item, "unit") && !empty($item->unit)) {
                            $unit = null;
                            if (property_exists($item->unit, "id")) {
                                $unit = $this->unitRepository->find($item->unit->id);
                            }
                            if (empty($unit)) {
                                $unit = new Unit();
                            }
                            $unit->setName($item->unit->name);
                            if (property_exists($item->unit, "readOnly")) {
                                $unit->setReadOnly($item->unit->readOnly);
                            } else {
                                $unit->setReadOnly(false);
                            }
                            $this->entityManager->persist($unit);
                            $attributeVal->setUnit($unit);
                        }
                        $this->entityManager->persist($attributeVal);
                        $active->addCustomAttributes($attributeVal);
                    endforeach;
                }
            endforeach;
        endif;

        $this->entityManager->flush();
        $this->attributeValueRepository->deleteOrphanedAttributes();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE]
        ];
    }
}