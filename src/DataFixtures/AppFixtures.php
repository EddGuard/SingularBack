<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(
        UserPasswordEncoderInterface $encoder
    ) {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $user = new User();
        $plainPassword = 'tfg-admin';

        $user->setEmail('superadmin@qiip.io');
        $user->setName('admin');
        $user->setLastName('admin');
        $user->setPassword($this->encoder->encodePassword($user, $plainPassword));
        $user->setUsername('admin-tfg');
        $manager->persist($user);

        $manager->flush();
    }
}
