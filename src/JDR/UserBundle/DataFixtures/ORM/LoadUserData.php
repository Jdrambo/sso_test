<?php

namespace JDRUserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JDR\UserBundle\Entity\User;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
	/**
	* @var ContainerInterface
	*/
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $em)
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setEmail('johann.derus@cardonnel.fr');
        $user->setIsActive(true);
        $user->setRoles(array('ROLE_ADMIN'));
        $user->setSalt(md5(uniqid()));

        $plainPassword = 'plokiju';
		$encoder = $this->container->get('security.password_encoder');
		$encoded = $encoder->encodePassword($user, $plainPassword);
		$user->setPassword($encoded);

        $em->persist($user);
        $em->flush();
    }
}