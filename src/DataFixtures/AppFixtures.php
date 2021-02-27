<?php

namespace App\DataFixtures;

use App\Entity\Roles;
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
        $rolesDefault = array(
            'taskmaster' => 500,
            'user' => 100,
            'admin' => 1000);



        foreach ($rolesDefault as $roles => $weight) {
            $role = new Roles();
            $role->setRole(strtoupper('ROLE_'.$roles));
            $role->setName($roles);
            $role->setWeight($weight);
            $manager->persist($role);
            $manager->flush();
        }
        $plainPassword = 'tfg-admin';

        $user->setEmail('admin@tfg.com');
        $user->setName('admin');
        $user->setLastName('admin');
        $user->addGroup($role);
        $user->setPassword($this->encoder->encodePassword($user, $plainPassword));
        $user->setUsername('admin-tfg');
        $user->setCreatedAt(new \DateTime());
        $manager->persist($user);

        $manager->flush();
    }
}
