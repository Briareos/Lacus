<?php

namespace Lacus\MainBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Application\Sonata\UserBundle\Entity\Group;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Application\Sonata\UserBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class LoadRoleData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $superAdmin = new Group('super_admin', array('ROLE_SUPER_ADMIN'));
        $this->addReference('group-super_admin', $superAdmin);
        $manager->persist($superAdmin);
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getOrder()
    {
        return 1;
    }
}