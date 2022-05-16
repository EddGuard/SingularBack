<?php
// api/src/Controller/CreateMediaObjectAction.php

namespace App\Controller;

use App\Entity\ActiveRecord;
use App\Repository\ActiveRecordRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActiveRecordController extends AbstractController
{

    /**
     * @Route("/api/active_records/{recordId}/attribute_info/{attributeId}", methods={"GET"}, name="api_acive_records_get_attribute_values")
     * @param Request $request
     * @param string $segmentUUID
     * @return JsonResponse
     */
    public function addConfigToSegment(Request $request, string $recordId, string $attributeId, ActiveRecordRepository $activeRecordRepository): JsonResponse
    {
        $result = [];
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        try {
            $record = $activeRecordRepository->findOneById($recordId);
            if ($record instanceof ActiveRecord) {
                $activeInstances = $record->getActiveObject();
                foreach ($activeInstances as $active) {
                    foreach ($active['basic_attributes'] as $attribute) {
                        $realId = array_key_exists('id', $attribute) ? $attribute['id'] : null;
                        if (($realId === (int)$attributeId)) {
                            $result[] = [
                                "id" => $realId,
                                "value" => $attribute['value'],
                                "date" => $active['updatedAt'],
                                "unit" => $attribute['unit']
                            ];
                        }
                    }
                }

            }
            return new JsonResponse($result, 200);

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            return new JsonResponse(
                $response,
                Response::HTTP_NOT_FOUND
            );
        }
    }
}