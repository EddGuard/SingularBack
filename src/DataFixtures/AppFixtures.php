<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $user = new User();

        $user->setName('Admin');
        $user->setLastName('Back');
        $user->setPassword($this->encoder->encodePassword($user, 'suP3rus3r'));
        $user->setEmail('admin@back.io');
        $user->setUsername('superadmin');
        $manager->persist($user);
        $manager->flush();
    }
}
