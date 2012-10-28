<?php

namespace Lacus\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Lacus\MainBundle\Content\ProviderPool;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class AccountAdmin extends Admin
{
    protected $baseRouteName = 'admin_lacus_main_account';

    protected $baseRoutePattern = 'account';

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
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with("General")
            ->add('username')
            ->add('password')
            ->add('site', null, array(
            'property' => 'name',
        ))
            ->end();
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('username');
    }

    protected function configureShowFields(ShowMapper $filter)
    {
        $filter
            ->add('username');
    }


}