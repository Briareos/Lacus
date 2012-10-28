<?php

namespace Lacus\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
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

    public function setProviderPool(ProviderPool $providerPool)
    {
        $this->providerPool = $providerPool;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        // Address prefix: site
        // Path prefix: admin_lacus_main_site_
        $collection->add('provider', 'provider/{provider}', array(
            '_controller' => 'MainBundle:SiteAdmin:provider',
        ), array(
            'provider' => '(' . implode('|', $this->providerPool->getProviderAliases()) . ')',
        ));
        $collection->add('provider_list', 'provider', array(
            '_controller' => 'MainBundle:SiteAdmin:providerList',
        ));
        $collection->add('provider_finalize', 'provider/{provider}/{mapperId}/finalize', array(
            '_controller' => 'MainBundle:SiteAdmin:providerFinalize',
        ));
        $collection->add('provider_post', 'provider/post/{mapperId}', array(
            '_controller' => 'MainBundle:SiteAdmin:providerPost',
        ));
        $collection->add('queue', 'queue', array(
            '_controller' => 'MainBundle:SiteAdmin:queue',
        ));
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with("General")
            ->add('name')
            ->add('url')
            ->end()
            ->with("Login data")
            ->add('loginUrl', null, array(
            'required' => false,
        ))
            ->add('loginUsername', null, array(
            'required' => false,
        ))
            ->add('loginPassword', null, array(
            'required' => false,
        ))
            ->add('loginButton', null, array(
            'required' => false,
        ))
            ->end();
    }

    protected function configureListFields(ListMapper $list)
    {
        $list->addIdentifier('name');

        $mapperAdmin = $this->getConfigurationPool()->getAdminByClass('Lacus\MainBundle\Entity\Mapper');
        if ($mapperAdmin->isGranted('LIST')) {
            $list->add('mappers', null, array(
                'template' => 'MainBundle:SiteAdmin:list_mappers.html.twig',
            ));
        }

        $postAdmin = $this->getConfigurationPool()->getAdminByClass('Lacus\MainBundle\Entity\Post');
        if ($postAdmin->isGranted('LIST')) {
            $list->add('posts', null, array(
                'template' => 'MainBundle:SiteAdmin:list_posts.html.twig',
            ));
        }

        $list->add('url');
    }

    protected function configureShowFields(ShowMapper $filter)
    {
        $filter
            ->add('name')
            ->add('url');
    }


}