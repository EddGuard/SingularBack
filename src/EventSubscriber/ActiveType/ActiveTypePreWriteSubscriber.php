<?php

namespace App\EventSubscriber\ActiveType;

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

class ActiveTypePreWriteSubscriber implements EventSubscriberInterface
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
        $type = $event->getControllerResult();
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        $content = json_decode($request->getContent());


        if (!($type instanceof ActiveType)) {
            return;
        }
        if ('api_activeTypes_post_collection' == $route || 'api_activeTypes_put_item' == $route):
            foreach ($content as $key => $field):
                if ($key == 'name') {
                    $type->setName($field);
                }
                if ($key == 'basicAttributes') {
                    foreach ($field as $item) {
                        $attributeVal = null;
                        if (property_exists($item, 'id')) {
                            $attributeVal = $this->attributeValueRepository->findOneBy(["id" => $item->id, "activeTypeBasics" => $type]);
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
                        $type->addBasicAttributes($attributeVal);
                        $this->entityManager->persist($type);
                    }
                } elseif ($key == 'customAttributes') {
                    foreach ($field as $item) {
                        $attributeVal = null;
                        if (property_exists($item, 'id')) {
                            $attributeVal = $this->attributeValueRepository->findOneBy(["id" => $item->id, "activeTypeCustoms" => $type]);
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
                        $type->addCustomAttributes($attributeVal);
                        $this->entityManager->persist($type);
                    }
                }
            endforeach;
        endif;
        $this->entityManager->flush();
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_WRITE]
        ];
    }
}