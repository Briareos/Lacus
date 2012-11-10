<?php

namespace Lacus\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Lacus\MainBundle\Content\ProviderPool;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Knp\Menu\ItemInterface as MenuItemInterface;

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
          ->add(
            'site',
            null,
            array(),
            null,
            array(
                'property' => 'name',
            )
        );
    }


    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add(
            'configure',
            '{id}/configure',
            array(
                '_controller' => 'MainBundle:MapperAdmin:configure',
            )
        );
        $collection->add(
            'get_fields',
            'get-fields',
            array(
                '_controller' => 'MainBundle:MapperAdmin:getFields',
            )
        );
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
          ->with("General")
          ->add('name')
          ->add(
            'active',
            null,
            array(
                'required' => false,
            )
        );
        if (!$this->getSubject()->getId()) {
            $form->add(
                'site',
                null,
                array(
                    'property' => 'name',
                )
            )
              ->add(
                'provider',
                'choice',
                array(
                    'choices' => array_combine($this->providerPool->getProviderAliases(), $this->providerPool->getProviderAliases()),
                )
            );
        }
        $form
          ->end()
          ->with("Posting options")
          ->add(
            'loginRequired',
            null,
            array(
                'required' => false,
            )
        )
          ->add(
            'defaultAccount',
            null,
            array(
                'required' => false,
                'property' => 'username',
            )
        )
          ->add('submitUrl')
          ->add('submitButton')
          ->end();
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
          ->addIdentifier('name')
          ->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'edit' => array(
                        'template' => 'MainBundle:MapperAdmin:list__action_edit.html.twig',
                    ),
                    'configure' => array(
                        'template' => 'MainBundle:MapperAdmin:list__action_configure.html.twig',
                    ),
                )
            )
        );
    }

    protected function configureShowFields(ShowMapper $filter)
    {
        $filter
          ->add('name')
          ->add(
            'site',
            null,
            array(
                'associated_tostring' => 'getName',
            )
        )
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

    public function getSecurityInformation()
    {
        return parent::getSecurityInformation() + array(
            'CONFIGURE' => array(),
        );
    }

    protected function configureSideMenu(MenuItemInterface $menu, $action, Admin $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit', 'configure'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            $this->trans('sidemenu.link_edit_mapper'),
            array(
                'uri' => $admin->generateUrl('edit', array('id' => $id)),
                'attributes' => array(
                    'class' => ($action === 'edit') ? 'active' : '',
                ),
            )
        );

        $menu->addChild(
            $this->trans('sidemenu.link_configure_mapper'),
            array(
                'uri' => $admin->generateUrl('configure', array('id' => $id)),
                'attributes' => array(
                    'class' => ($action === 'configure') ? 'active' : '',
                ),
            )
        );
    }


}