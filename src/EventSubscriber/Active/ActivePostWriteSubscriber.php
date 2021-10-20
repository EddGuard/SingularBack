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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;



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
    /**
     * @var TokenStorageInterface
     */
    private TokenStorageInterface $tokenStorage;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        AttributeValueRepository $attributeValueRepository, UnitRepository $unitRepository,
        TokenStorageInterface $tokenStorage,
        SerializerInterface $serializer
    )
    {
        $this->entityManager = $entityManager;
        $this->attributeValueRepository = $attributeValueRepository;
        $this->unitRepository = $unitRepository;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
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
        $user = $this->tokenStorage->getToken()->getUser();


        if (!($active instanceof Active)) {
            return;
        }
        if ('api_actives_post_collection' == $route) {

            $this->entityManager->getConnection()->beginTransaction();
            try {

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
                $today = new \DateTime();
                $activeToSave->entry_date = $today->format("d/m/Y H:i:s");
                $activeToSave->file = $active->getFile() ? $active->getFile()->getContentUrl() : null;
                $activeToSave->type = $type;
                $activeToSave->description = $content->description;
                $activeToSave->user = json_decode($this->serializer->serialize($user, 'json'));
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
        } elseif ('api_actives_put_item' == $route) {
            $this->entityManager->getConnection()->beginTransaction();

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
            $today = new \DateTime();
            $activeToSave->entry_date = $today->format("d/m/Y H:i:s");

            $activeToSave->file = $active->getFile() ? $active->getFile()->getContentUrl() : null;
            $activeToSave->type = $type;
            $activeToSave->description = $content->description;
            $activeToSave->user = json_decode($this->serializer->serialize($user, 'json'));
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