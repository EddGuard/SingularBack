<?php

namespace App\Controller\MediaObject;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\MediaObject;
use App\Exception\MediaObjectException;
use App\Services\AwsS3Service;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GetMediaObjectAction
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
     * @return Response
     * @throws MediaObjectException
     */
    public function __invoke(MediaObject $mediaObject)
    {
        try {
            $result = $this->awsS3Service->downloadFile($mediaObject->getPath());
        } catch (\Exception $e) {
            throw new MediaObjectException($this->translator->trans('File not found'));
        }

        if ($result['Body'] != null) {
            $headers = array('Content-Type' => $mediaObject->getMimetype());

            return new Response($result['Body'], Response::HTTP_OK, $headers);
        } else {
            throw new MediaObjectException($this->translator->trans('File not found'));
        }
    }
}
