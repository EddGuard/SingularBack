<?php
/**
 * Created by PhpStorm.
 * User: SUSAN MEDINA
 * Date: 23/05/2019
 * Time: 04:15 PM
 */

namespace App\Services;

use App\Repository\ActiveRecordRepository;
use Exception;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ActiveRecordService
{
    private $entityManager;
    private $tokenStorage;
    private $translator;
    private $requestStack;
    /**
     * @var ActiveRecordRepository
     */
    private ActiveRecordRepository $activeRecordRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        ActiveRecordRepository $activeRecordRepository
    ){

        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->activeRecordRepository = $activeRecordRepository;
    }


    /**
     * @param array $data
     * @return array|Response
     * @throws Exception
     */
    public function formatNormalize(array &$data)
    {
        $record = $this->activeRecordRepository->findOneById($data['id']);

        if (isset($data['activeObject']) && !is_null($data['activeObject'])) {
            $activeObject = $record->getActiveObject();
            foreach ($activeObject as $key=>$item){
                $data['activeObject'][$key] = $item;
            }
        }

        return $data;
    }

}