<?php
// src/Controller/CreateMediaObjectAction.php

namespace App\Controller\MediaObject;

use App\Entity\User;
use Exception;
use Imagick;
use ImagickPixel;
use App\Entity\MediaObject;
use App\Form\MediaObjectType;
use App\Services\AwsS3Service;
use App\Services\UtilsService;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;

final class CreateMediaObjectAction extends AbstractController
{
    private $validator;
    private $doctrine;
    private $factory;
    private $tokenStorage;
    private $awsS3Service;
    private $checker;
    private $serializer;
    private $bus;
    private $utilsService;
    private $ticketRepository;
    private $taskRepository;
    private $translator;

    const BIG = 'BIG';
    const SMALL = 'SMALL';

    public function __construct(
        RegistryInterface $doctrine,
        FormFactoryInterface $factory,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage,
        AwsS3Service $awsS3Service,
        AuthorizationCheckerInterface $checker,
        SerializerInterface $serializer,
        MessageBusInterface $bus,
        UtilsService $utilsService,
        TicketRepository $ticketRepository,
        TaskRepository $taskRepository,
        TranslatorInterface $translator
    ) {
        $this->validator = $validator;
        $this->doctrine = $doctrine;
        $this->factory = $factory;
        $this->tokenStorage = $tokenStorage;
        $this->awsS3Service = $awsS3Service;
        $this->checker = $checker;
        $this->serializer = $serializer;
        $this->bus = $bus;
        $this->utilsService = $utilsService;
        $this->ticketRepository = $ticketRepository;
        $this->taskRepository = $taskRepository;
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     * @return MediaObject|Response
     * @throws \ImagickException
     */
    public function __invoke(Request $request)
    {
        $token = $this->tokenStorage->getToken();

        $locale = $request->getLocale();
        if($token->getUser() instanceof User) {
           $locale = $token->getUser()->getLocale();
        }

        $mediaObject = new MediaObject();

        $form = $this->factory->create(MediaObjectType::class, $mediaObject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->doctrine->getManager();
            $entityId = $form->getData()->getEntityId();

            switch ($mediaObject->getType()) {
                case MediaObject::TYPE_TICKET:
                    $mediaObject = $this->addMediaObjectToTicket($entityId, $mediaObject);
                    break;
                case MediaObject::TYPE_TASK:
                    $mediaObject = $this->addMediaObjectToTask($entityId, $mediaObject);
                    break;
                default:
                    break;
            }
/*
            switch (true) {
                case (filesize($mediaObject->file) > MediaObject::max_size_image):
                    throw new \App\Exception\InvalidArgumentException(
                        $this->translator->trans("max mb", ['%mb%' => MediaObject::max_size_image / 1000000])
                    );
                    break;
                case (filesize($mediaObject->file) > MediaObject::max_size_audio):
                    throw new \App\Exception\InvalidArgumentException(
                        $this->translator->trans("max mb", ['%mb%' => MediaObject::max_size_audio / 1000000])
                    );
                    break;
                case (filesize($mediaObject->file) > MediaObject::max_size_video):
                    throw new \App\Exception\InvalidArgumentException(
                        $this->translator->trans("max mb", ['%mb%' => MediaObject::max_size_video / 1000000])
                    );
                    break;
                case (filesize($mediaObject->file) > MediaObject::max_size_files):
                    throw new \App\Exception\InvalidArgumentException(
                        $this->translator->trans("max mb", ['%mb%' => MediaObject::max_size_files / 1000000])
                    );
                    break;
                default:
                    break;
            }
*/
            $originalName = $mediaObject->file->getClientOriginalName();
            $path = $this->awsS3Service->uploadFile($mediaObject->file);
            $imageMimeTypes = array('image/jpeg', 'image/jpg', 'image/png');

            $mediaObject->setName($originalName);
            $mediaObject->setMimetype($mediaObject->file->getMimeType());
            $mediaObject->setPath($path);
            $mediaObject->setSize(filesize($mediaObject->file));

            //throw new \Exception($mediaObject->getMimetype());

            if (in_array($mediaObject->getMimetype(),$imageMimeTypes)) {
                //Thumbnail small
                $thumbnailPath = $this->generateThumbnail($mediaObject->file, 90, $this::SMALL);
                $thumbnailFile = new UploadedFile($thumbnailPath, $originalName);
                $thumbnailPath = $this->awsS3Service->uploadFile($thumbnailFile, true);
                $mediaObject->setPathThumbnailSmall($thumbnailPath);
                //Thumbnail big
                $thumbnailPath = $this->generateThumbnail($mediaObject->file, 90, $this::BIG);
                $thumbnailFile = new UploadedFile($thumbnailPath, $originalName);
                $thumbnailPath = $this->awsS3Service->uploadFile($thumbnailFile, true);
                $mediaObject->setPathThumbnailBIG($thumbnailPath);
            }
            else {
                $mediaObject->setPathThumbnailSmall('');
                $mediaObject->setPathThumbnailBIG('');
            }

            $em->persist($mediaObject);
            $em->flush();

            return $mediaObject;
        }

        if (!in_array($mediaObject->file->getMimeType(), [
            "application/pdf", "application/x-pdf",
            "image/jpeg", "image/jpg", "image/png", "application/octet-stream",
            "video/mp4", "video/m4v", "video/x-m4v", "video/JPEG", "video/quicktime", "video/x-msvideo",
            "audio/mp4", "audio/mpeg", "text/plain",
            "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.ms-powerpoint", "application/vnd.openxmlformats-officedocument.presentationml.presentation"
        ])) {
            $response = new Response();
            $response->setContent(json_encode([
                'detail' => $this->translator->trans('mime.type.valid', [], null, $locale)
            ]));
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        if (filesize($mediaObject->file) > 20000000) {
            $response = new Response();
            $response->setContent(json_encode([
                'detail' => $this->translator->trans('mime.max.size', [], null, $locale)
            ]));
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            return $response;
        }

        $response = new Response();
        $response->setContent(json_encode([
            'detail' => $this->validator->validate($mediaObject)
        ]));
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);

        return $response;
    }

    /**
     * Generate Thumbnail using Imagick class
     *
     * @param $img
     * @param int $quality
     * @param $type
     * @return string
     * @throws \ImagickException
     * @throws Exception
     */
    public function generateThumbnail($img, $quality = 90, $type)
    {
        if (is_file($img)) {
            $imagick = new Imagick(realpath($img));
            $imagick->setBackgroundColor(new ImagickPixel('transparent')); //white
            $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
            $imagick->setImageCompressionQuality($quality);
            $imagick->setIteratorIndex(0);
            $filename_no_ext = explode('.', $img);
            $fileName = $filename_no_ext[0];

            if ($type == $this::BIG) {
                $imagick->thumbnailImage(1024, 1024, true, false);
                $fileName = $filename_no_ext[0] . '_thumb_big.jpeg';
            } elseif ($type == $this::SMALL) {
                $imagick->thumbnailImage(128, 128, true, false);
                $fileName = $filename_no_ext[0] . '_thumb_small.jpeg';
            }

            $imagick->setImageFormat('jpeg');

            if (file_put_contents($fileName, $imagick) === false) {
                throw new Exception($this->translator->trans('Could not put contents'));
            }

            return $fileName;
        } else {
            throw new Exception($this->translator->trans('No valid image provided with', ['%image%' => $img]));
        }
    }

    /**
     * @param int $entityId
     * @param MediaObject $mediaObject
     * @return MediaObject
     */
    protected function addMediaObjectToTicket($entityId, MediaObject &$mediaObject)
    {
        if (isset($entityId) && is_integer($entityId)) {
            $ticket = $this->ticketRepository->find($entityId);
            if (!$ticket)
                throw new ValidationException($this->validator->validate($mediaObject));

            $mediaObject->setTicket($ticket);
        }

        return $mediaObject;
    }

    /**
     * @param int $entityId
     * @param MediaObject $mediaObject
     * @return MediaObject
     */
    protected function addMediaObjectToTask($entityId, MediaObject &$mediaObject)
    {
        if (isset($entityId) && is_integer($entityId)) {
            $task = $this->taskRepository->find($entityId);
            if (!$task)
                throw new ValidationException($this->validator->validate($mediaObject));

            $mediaObject->setTask($task);
        }

        return $mediaObject;
    }
}
