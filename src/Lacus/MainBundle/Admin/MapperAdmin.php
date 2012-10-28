<?php

namespace Lacus\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Lacus\MainBundle\Content\ProviderPool;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class MapperAdmin extends Admin
{
    protected $baseRouteName = 'admin_lacus_main_mapper';

    protected $baseRoutePattern = 'mapper';

    /**
     * @var ProviderPool
     */
    private $providerPool;

    public function setProviderPool(ProviderPool $providerPool)
    {
        $this->providerPool = $providerPool;
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('site', null, array(), null, array(
            'property' => 'name',
        ));
    }


    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('configure', '{id}/configure', array(
            '_controller' => 'MainBundle:MapperAdmin:configure',
        ));
        $collection->add('get_fields', 'get-fields', array(
            '_controller' => 'MainBundle:MapperAdmin:getFields',
        ));
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with("General")
            ->add('name')
            ->add('active', null, array(
            'required' => false,
        ));
        if (!$this->getSubject()->getId()) {
            $form->add('site', null, array(
                'property' => 'name',
            ))
                ->add('provider', 'choice', array(
                'choices' => array_combine($this->providerPool->getProviderAliases(), $this->providerPool->getProviderAliases()),
            ));
        }
        $form
            ->end()
            ->with("Posting options")
            ->add('loginRequired', null, array(
            'required' => false,
        ))
            ->add('defaultAccount', null, array(
            'required' => false,
            'property' => 'username',
        ))
            ->add('submitUrl')
            ->add('submitButton')
            ->end();
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('name')
            ->add('info');
    }

    protected function configureShowFields(ShowMapper $filter)
    {
        $filter
            ->add('name')
            ->add('site', null, array(
            'associated_tostring' => 'getName',
        ))
            ->add('provider')
            ->add('loginRequired')
            ->add('submitUrl')
            ->add('submitButton');
    }

    public function toString($object)
    {
        /** @var $object \Lacus\MainBundle\Entity\Mapper */
        return $object->getName() . " mapper: " . $object->getInfo();
    }

    public function getTemplate($name)
    {
        if ($name === 'edit') {
            return 'MainBundle:MapperAdmin:edit.html.twig';
        }
        return parent::getTemplate($name);
    }


}