<?php


namespace App\Services;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class UserService
{
    private $encoder;
    private $em;

    public function __construct(
        UserPasswordEncoderInterface $encoder,
        EntityManagerInterface $em
    ){
        $this->encoder = $encoder;
        $this->em = $em;
    }
/**
* @param User $user
* @param bool $update
* @param bool $flush
* @return User
* @throws \Doctrine\ORM\ORMException
*/
public function encodePassword(User $user, $update = false, $flush = true)
{
if (!$this->em->isOpen()) {
$this->em = EntityManager::create(
$this->em->getConnection(),
$this->em->getConfiguration()
);
}

if(false == $this->em->getConnection()->ping()){
$this->em->getConnection()->close();
$this->em->getConnection()->connect();
}

$encoded = $this->encoder->encodePassword($user, $user->getPlainPassword());
if ($update) {
if ($encoded !== $user->getPassword()) {
$user->setPassword($encoded);
}
} else {
$user->setPassword($encoded);
}

$this->em->persist($user);

if ($flush)
$this->em->flush();

return $user;
}
}