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
     * @param string $recordId
     * @param string $attributeId
     * @param ActiveRecordRepository $activeRecordRepository
     * @return JsonResponse
     */
    public function getAttributeInfo(Request $request, string $recordId, string $attributeId, ActiveRecordRepository $activeRecordRepository): JsonResponse
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

    /**
     * @Route("/api/active_records/{recordId}/active_dates", methods={"GET"}, name="api_acive_records_get_attribute_values")
     * @param Request $request
     * @param string $recordId
     * @param ActiveRecordRepository $activeRecordRepository
     * @return JsonResponse
     */
    public function getRecordByDate(Request $request, string $recordId, ActiveRecordRepository $activeRecordRepository): JsonResponse
    {
        $result = [];
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $urlParts = parse_url(filter_var($request->getUri(), FILTER_SANITIZE_URL));
        $queryParts = array();
        if (array_key_exists('query', $urlParts)) {
            parse_str($urlParts['query'], $queryParts);
        }
        try {
            $record = $activeRecordRepository->findOneById($recordId);
            if (array_key_exists("date", $queryParts)) {
                $dateToFind = new \DateTime($queryParts["date"]);
                if ($record instanceof ActiveRecord) {
                    $activeInstances = $record->getActiveObject();
                    foreach ($activeInstances as $active) {
                        $date = $active['updatedAt']['date'];
                        $dateTime = new \DateTime($date);

                        if ($dateTime->format('d-m-Y') === $dateToFind->format('d-m-Y')) {
                            $result[] = $active;
                        }
                    }

                }
            } else {
                $result = $record->getActiveObject();
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