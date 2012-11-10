<?php

namespace Lacus\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Lacus\MainBundle\Content\ProviderPool;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class SiteAdmin extends Admin
{
    protected $baseRouteName = 'admin_lacus_main_site';

    protected $baseRoutePattern = 'site';

    /**
     * @var ProviderPool
     */
    private $providerPool;

    private $securityContext;

    public function setProviderPool(ProviderPool $providerPool)
    {
        $this->providerPool = $providerPool;
    }

    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
          ->with("General")
          ->add('name')
          ->add('url')
          ->end()
          ->with("Management")
          ->add('users')
          ->end()
          ->with("Login data")
          ->add(
            'loginUrl',
            null,
            array(
                'required' => false,
            )
        )
          ->add(
            'loginUsername',
            null,
            array(
                'required' => false,
            )
        )
          ->add(
            'loginPassword',
            null,
            array(
                'required' => false,
            )
        )
          ->add(
            'loginButton',
            null,
            array(
                'required' => false,
            )
        )
          ->end();
    }

    protected function configureListFields(ListMapper $list)
    {
        $list->addIdentifier('name');

        $mapperAdmin = $this->getConfigurationPool()->getAdminByClass('Lacus\MainBundle\Entity\Mapper');
        if ($mapperAdmin->isGranted('LIST')) {
            $list->add(
                'mappers',
                null,
                array(
                    'template' => 'MainBundle:SiteAdmin:list_mappers.html.twig',
                )
            );
        }

        $postAdmin = $this->getConfigurationPool()->getAdminByClass('Lacus\MainBundle\Entity\Post');
        if ($postAdmin->isGranted('LIST')) {
            $list->add(
                'posts',
                null,
                array(
                    'template' => 'MainBundle:SiteAdmin:list_posts.html.twig',
                )
            );
        }

        $list->add(
            'url',
            null,
            array()
        );
    }

    protected function configureShowFields(ShowMapper $filter)
    {
        $filter
          ->add('name')
          ->add('url');
    }

    public function isGranted($name, $object = null)
    {
        /** @var $object \Lacus\MainBundle\Entity\Site */
        if ($name === 'VIEW') {
            return $this->isGranted('LIST') || ($this->isGranted('EDIT_OWN') && $object->getOwner() && $this->getUser() && $object->getOwner()->getId() === $this->getUser()->getId());
        }

        return parent::isGranted($name, $object);
    }

    /**
     * @return null|\Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    public function createQuery($context = 'list')
    {
        if ($context === 'list') {
            /** @var $query \Doctrine\ORM\QueryBuilder */
            $query = $this->getModelManager()->createQuery($this->getClass());
            $query->leftJoin('o.mappers', 'm');
            $query->addSelect('m');

            foreach ($this->extensions as $extension) {
                $extension->configureQuery($this, $query, $context);
            }

            return $query;
        }

        return parent::createQuery($context);
    }


    public function getSecurityInformation()
    {
        return parent::getSecurityInformation() + array(
            'EDIT_OWN' => array(),
        );
    }


}
