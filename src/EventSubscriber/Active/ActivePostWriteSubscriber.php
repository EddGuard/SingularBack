<?php

namespace App\EventSubscriber\Active;

use App\Entity\Active;
use App\Entity\ActiveRecord;
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

class ActivePostWriteSubscriber implements EventSubscriberInterface
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
        if ('api_actives_post_collection' == $route) {

            $this->entityManager->getConnection()->beginTransaction();
            try {
                //Seteo de attribute values definidos por defecto en el type al activo
                $type = $active->getActiveType();

                foreach ($type->getBasicAttributes() as $basicAttribute):
                    $attributeValue = new AttributeValue();
                    $attributeValue->setName($basicAttribute->getName());
                    $attributeValue->setValue($basicAttribute->getValue());
                    if (!empty($basicAttribute->getUnit())) {
                        $attributeValue->setUnit($basicAttribute->getUnit());
                    }
                    $this->entityManager->persist($attributeValue);
                    $active->addBasicAttributes($attributeValue);
                    $this->entityManager->persist($active);
                endforeach;
                foreach ($type->getCustomAttributes() as $customAttribute):
                    $attributeValue = new AttributeValue();
                    $attributeValue->setName($customAttribute->getName());
                    $attributeValue->setValue($customAttribute->getValue());
                    if (!empty($customAttribute->getUnit())) {
                        $attributeValue->setUnit($customAttribute->getUnit());
                    }
                    $this->entityManager->persist($attributeValue);
                    $active->addCustomAttributes($attributeValue);
                    $this->entityManager->persist($active);
                endforeach;

                foreach ($content as $key => $items):
                    if ($key == 'basicAttributes'):
                        foreach ($items as $item):
                            if (property_exists($item, 'id')):
                                $existingAttribute = $this->attributeValueRepository->findOneBy(["id" => $item->id, "activeBasics" => $active]);
                            endif;
                            if (!empty($existingAttribute)):
                                $existingAttribute->setName($item->name);
                                $existingAttribute->setValue($item->value);
                                if (property_exists($item, 'unit')):
                                    $existingAttribute->getUnit()->setName($item->unit->name);
                                endif;
                                $active->addBasicAttributes($existingAttribute);
                                $this->entityManager->persist($active);
                                $existingAttribute = null;
                            else:
                                $attributeVal = new AttributeValue();
                                $attributeVal->setName($item->name);
                                $attributeVal->setValue($item->value);
                                if (property_exists($item, 'unit')):
                                    $unit = new Unit();
                                    $unit->setName($item->unit->name);
                                    if (property_exists($item->unit, 'readOnly')):
                                        $unit->setReadOnly($item->unit->readOnly);
                                    else:
                                        $unit->setReadOnly(false);
                                    endif;
                                    $this->entityManager->persist($unit);
                                    $attributeVal->setUnit($unit);
                                endif;
                                $this->entityManager->persist($attributeVal);
                                $active->addBasicAttributes($attributeVal);
                            endif;
                        endforeach;
                    elseif ($key == 'customAttributes'):
                        foreach ($items as $item):
                            if (property_exists($item, 'id')):
                                $existingAttribute = $this->attributeValueRepository->findOneBy(["id" => $item->id, "activeCustoms" => $active]);
                            endif;
                            if (!empty($existingAttribute)):
                                $existingAttribute->setName($item->name);
                                $existingAttribute->setValue($item->value);
                                if (property_exists($item, 'unit')):
                                    $existingAttribute->getUnit()->setName($item->unit->name);
                                endif;
                                $active->addCustomAttributes($existingAttribute);
                                $this->entityManager->persist($active);
                                $existingAttribute = null;
                            else:
                                $attributeVal = new AttributeValue();
                                $attributeVal->setName($item->name);
                                $attributeVal->setValue($item->value);
                                if (property_exists($item, 'unit')):
                                    $unit = new Unit();
                                    $unit->setName($item->unit->name);
                                    if (property_exists($item->unit, 'readOnly')):
                                        $unit->setReadOnly($item->unit->readOnly);
                                    else:
                                        $unit->setReadOnly(false);
                                    endif;
                                    $this->entityManager->persist($unit);
                                    $attributeVal->setUnit($unit);
                                endif;
                                $this->entityManager->persist($attributeVal);
                                $active->addCustomAttributes($attributeVal);
                            endif;
                        endforeach;
                    endif;
                endforeach;;
                $this->entityManager->flush();


                //Creación de primera entrada en el registro del activo

                $record = new ActiveRecord();
                $record->setActive($active);

                $dateRecord = $record->getDateRecord();
                $dateRecord[] = new \DateTime();
                $record->setDateRecord($dateRecord);

                $activeObject = $record->getActiveObject();

                $basicAttributes = [];
                foreach ($active->getBasicAttributes() as $key => $attributeValue) {
                    $basicAttributes[$key]["name"] = $attributeValue->getName();
                    $basicAttributes[$key]["value"] = $attributeValue->getValue();
                    if (!empty($attributeValue->getUnit())) {
                        $basicAttributes[$key]["unit"]["id"] = $attributeValue->getUnit()->getId();
                        $basicAttributes[$key]["unit"]["name"] = $attributeValue->getUnit()->getName();
                    } else {
                        $basicAttributes[$key]["unit"] = null;
                    }
                }

                $customAttributes = [];
                foreach ($active->getCustomAttributes() as $key => $attributeValue) {
                    $customAttributes[$key]["name"] = $attributeValue->getName();
                    $customAttributes[$key]["value"] = $attributeValue->getValue();
                    if (!empty($attributeValue->getUnit())) {
                        $customAttributes[$key]["unit"]["id"] = $attributeValue->getUnit()->getId();
                        $customAttributes[$key]["unit"]["name"] = $attributeValue->getUnit()->getName();
                    } else {
                        $customAttributes[$key]["unit"] = null;
                    }
                }
                $type = [];
                $type["id"] = $active->getActiveType()->getId();
                $type["name"] = $active->getActiveType()->getName();

                $activeToSave = new \stdClass();
                $activeToSave->reference = $active->getReference();
                $activeToSave->entry_date = $active->getEntryDate()->format("d/m/Y H:i:s");
                $activeToSave->file = $active->getFile() ? $active->getFile()->getContentUrl() : null;
                $activeToSave->type = $type;
                $activeToSave->basic_attributes = $basicAttributes;
                $activeToSave->custom_attributes = $customAttributes;

                $activeObject[] = $activeToSave;
                $record->setActiveObject($activeObject);

                $this->entityManager->persist($record);

                $active->setActiveRecord($record);


                $this->entityManager->flush();
                $this->entityManager->getConnection()->commit();
            } catch (\Exception $exception) {
                $this->entityManager->getConnection()->rollback();
                //$this->entityManager->clear();

                throw new GeneralException($exception->getMessage());
            }
        }
        elseif ('api_actives_put_item' == $route) {
            $this->entityManager->getConnection()->beginTransaction();

            foreach ($content as $key => $items):
                if ($key == 'basicAttributes'):
                    foreach ($items as $item):
                        if (property_exists($item, 'id')):
                            $existingAttribute = $this->attributeValueRepository->findOneBy(["id" => $item->id, "activeBasics" => $active]);
                        endif;
                        if (!empty($existingAttribute)):
                            $existingAttribute->setName($item->name);
                                $existingAttribute->setValue($item->value);
                                if (property_exists($item, 'unit')):
                                    $existingAttribute->getUnit()->setName($item->unit->name);
                                endif;
                            $active->addBasicAttributes($existingAttribute);
                            $this->entityManager->persist($active);
                            $existingAttribute = null;
                        else:
                            $attributeVal = new AttributeValue();
                            $attributeVal->setName($item->name);
                            $attributeVal->setValue($item->value);
                            if (property_exists($item, 'unit')):
                                $unit = new Unit();
                                $unit->setName($item->unit->name);
                                if (property_exists($item->unit, 'readOnly')):
                                    $unit->setReadOnly($item->unit->readOnly);
                                else:
                                    $unit->setReadOnly(false);
                                endif;
                                $this->entityManager->persist($unit);
                                $attributeVal->setUnit($unit);
                            endif;
                            $this->entityManager->persist($attributeVal);
                            $active->addBasicAttributes($attributeVal);
                        endif;
                    endforeach;
                elseif ($key == 'customAttributes'):
                    foreach ($items as $item):
                        if (property_exists($item, 'id')):
                            $existingAttribute = $this->attributeValueRepository->findOneBy(["id" => $item->id, "activeCustoms" => $active]);
                        endif;
                        if (!empty($existingAttribute)):
                            $existingAttribute->setName($item->name);
                                $existingAttribute->setValue($item->value);
                                if (property_exists($item, 'unit')):
                                    $existingAttribute->getUnit()->setName($item->unit->name);
                                endif;
                            $active->addCustomAttributes($existingAttribute);
                            $this->entityManager->persist($active);
                            $existingAttribute = null;
                        else:
                            $attributeVal = new AttributeValue();
                            $attributeVal->setName($item->name);
                            $attributeVal->setValue($item->value);
                            if (property_exists($item, 'unit')):
                                $unit = new Unit();
                                $unit->setName($item->unit->name);
                                if (property_exists($item->unit, 'readOnly')):
                                    $unit->setReadOnly($item->unit->readOnly);
                                else:
                                    $unit->setReadOnly(false);
                                endif;
                                $this->entityManager->persist($unit);
                                $attributeVal->setUnit($unit);
                            endif;
                            $this->entityManager->persist($attributeVal);
                            $active->addCustomAttributes($attributeVal);
                        endif;
                    endforeach;
                endif;
            endforeach;

            $record = $active->getActiveRecord() ? $active->getActiveRecord() : new ActiveRecord();
            $record->setActive($active);

            $dateRecord = $record->getDateRecord();
            $dateRecord[] = new \DateTime();
            $record->setDateRecord($dateRecord);

            $activeObject = $record->getActiveObject();

            $basicAttributes = [];
            foreach ($active->getBasicAttributes() as $key => $attributeValue) {
                $basicAttributes[$key]["name"] = $attributeValue->getName();
                $basicAttributes[$key]["value"] = $attributeValue->getValue();
                if (!empty($attributeValue->getUnit())) {
                    $basicAttributes[$key]["unit"]["id"] = $attributeValue->getUnit()->getId();
                    $basicAttributes[$key]["unit"]["name"] = $attributeValue->getUnit()->getName();
                } else {
                    $basicAttributes[$key]["unit"] = null;
                }
            }

            $customAttributes = [];
            foreach ($active->getCustomAttributes() as $key => $attributeValue) {
                $customAttributes[$key]["name"] = $attributeValue->getName();
                $customAttributes[$key]["value"] = $attributeValue->getValue();
                if (!empty($attributeValue->getUnit())) {
                    $customAttributes[$key]["unit"]["id"] = $attributeValue->getUnit()->getId();
                    $customAttributes[$key]["unit"]["name"] = $attributeValue->getUnit()->getName();
                } else {
                    $customAttributes[$key]["unit"] = null;
                }
            }
            $type = [];
            $type["id"] = $active->getActiveType()->getId();
            $type["name"] = $active->getActiveType()->getName();

            $activeToSave = new \stdClass();
            $activeToSave->reference = $active->getReference();
            $activeToSave->entry_date = $active->getEntryDate()->format("d/m/Y H:i:s");
            $activeToSave->file = $active->getFile() ? $active->getFile()->getContentUrl() : null;
            $activeToSave->type = $type;
            $activeToSave->basic_attributes = $basicAttributes;
            $activeToSave->custom_attributes = $customAttributes;

            $activeObject[] = $activeToSave;
            $record->setActiveObject($activeObject);

            $this->entityManager->persist($record);

            $active->setActiveRecord($record);

            $this->entityManager->persist($active);

            $this->entityManager->flush();

            $this->entityManager->getConnection()->commit();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::POST_WRITE]
        ];
    }
}