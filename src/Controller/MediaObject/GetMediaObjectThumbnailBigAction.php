<?php

namespace App\Controller\MediaObject;

use App\Entity\App\MediaObject;
use App\Exception\MediaObjectException;
use App\Services\AwsS3Service;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GetMediaObjectThumbnailBigAction
{
    private $awsS3Service;
    private $translator;

    public function __construct(
        AwsS3Service $awsS3Service,
        TranslatorInterface $translator)
    {
        $this->awsS3Service = $awsS3Service;
        $this->translator = $translator;
    }

    /**
     * @param MediaObject $mediaObject
     * @return Response|void
     * @throws MediaObjectException
     */
    public function __invoke(MediaObject $mediaObject)
    {
        if (is_null($mediaObject->getPathThumbnailBig()) || $mediaObject->getPathThumbnailBig() === '') {
            return;
        }

        try {
            $result = $this->awsS3Service->downloadFile($mediaObject->getPathThumbnailBig());
        } catch (\Exception $e) {
            throw new MediaObjectException($this->translator->trans('Image not found'));
        }

        if ($result['Body'] != null) {
            $headers = array('Content-Type' => 'image/jpeg');
            return new Response($result['Body'], Response::HTTP_OK, $headers);
        } else {
            throw new MediaObjectException($this->translator->trans('Image not found'));
        }
    }
}
