<?php

namespace Lacus\MainBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Application\Sonata\UserBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    function load(ObjectManager $manager)
    {
        /** @var $userManager \FOS\UserBundle\Entity\UserManager */
        $userManager = $this->container->get('fos_user.user_manager');

        $admin = new User();
        $admin->setUsername("Gray Fox");
        $admin->setPlainPassword("metalfox");
        $admin->setEmail("gmefox@gmail.com");
        $admin->setEnabled(true);
        $superAdminGroup = $this->getReference('group-super_admin');
        $admin->addGroup($superAdminGroup);
        $this->addReference('user-admin', $admin);

        $userManager->updateUser($admin, true);

    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    function getOrder()
    {
        return 2;
    }
}