<?php

namespace Lacus\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Lacus\MainBundle\Entity\Post;
use Lacus\MainBundle\Form\Event\GeneratePostForm;
use Lacus\MainBundle\Content\ProviderPool;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class PostAdmin extends Admin
{
    protected $baseRouteName = 'admin_lacus_main_post';

    protected $baseRoutePattern = 'post';

    protected $datagridValues = array(
        '_sort_by' => 'createdAt',
        '_sort_order' => 'DESC',
    );

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
        $collection->add(
            'provider',
            'provider/{provider}',
            array(
                '_controller' => 'MainBundle:PostAdmin:provider',
            ),
            array(
                'provider' => '(' . implode('|', $this->providerPool->getProviderAliases()) . ')',
            )
        );
        $collection->add(
            'provider_list',
            'provider',
            array(
                '_controller' => 'MainBundle:PostAdmin:providerList',
            )
        );
        $collection->add(
            'provider_finalize',
            'provider/{provider}/{mapperId}/finalize',
            array(
                '_controller' => 'MainBundle:PostAdmin:providerFinalize',
            )
        );
        $collection->add(
            'provider_post',
            'provider/post/{mapperId}',
            array(
                '_controller' => 'MainBundle:PostAdmin:providerPost',
            )
        );
        $collection->add(
            'queue',
            'queue',
            array(
                '_controller' => 'MainBundle:PostAdmin:queue',
            )
        );
        $collection->add(
            'check',
            'check',
            array(
                '_controller' => 'MainBundle:PostAdmin:check',
            )
        );
        $collection->add(
            'check',
            'check',
            array(
                '_controller' => 'MainBundle:PostAdmin:check',
            )
        );
        $collection->add(
            'check_provider',
            'check/{provider}',
            array(
                '_controller' => 'MainBundle:PostAdmin:check',
            )
        );
    }

    public function getFormBuilder()
    {
        $formBuilder = parent::getFormBuilder();
        $formBuilder->addEventSubscriber(new GeneratePostForm($formBuilder->getFormFactory(), $this->getContentTemplate()));

        return $formBuilder;
    }

    public function getTemplate($name)
    {
        if ($name === 'edit') {
            return 'MainBundle:PostAdmin:edit.html.twig';
        }

        return parent::getTemplate($name);
    }


    protected function configureFormFields(FormMapper $form)
    {
        $form
          ->with("General")
          ->add('status')
          ->end()
          ->with("Content")
          ->end();
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
          ->addIdentifier('createdAt')
          ->add(
            'status',
            null,
            array(
                'template' => 'MainBundle:PostAdmin:list_status.html.twig',
            )
        );
    }

    protected function configureShowFields(ShowMapper $filter)
    {
        $filter
          ->add('id')
          ->add('createdAt');
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
          ->add(
            'status',
            null,
            array(),
            'choice',
            array(
                'choices' => array(
                    Post::STATUS_DRAFT => Post::STATUS_DRAFT,
                    Post::STATUS_REVIEW => Post::STATUS_REVIEW,
                    Post::STATUS_QUEUE => Post::STATUS_QUEUE,
                    Post::STATUS_PUBLISH => Post::STATUS_PUBLISH,
                ),
            )
        )
          ->add(
            'site',
            'doctrine_orm_callback',
            array(
                'callback' => function ($queryBuilder, $alias, $field, $value) {
                    if ($value['value'] === null) {
                        return;
                    }
                    /** @var $queryBuilder \Doctrine\ORM\QueryBuilder */
                    $queryBuilder->innerJoin(sprintf('%s.mapper', $alias), 'm');
                    $queryBuilder->innerJoin('m.site', 's');
                    $queryBuilder->andWhere('s = :site');
                    $queryBuilder->setParameter(':site', $value['value']);
                },
            ),
            'entity',
            array(
                'label' => 'Site',
                'class' => 'Lacus\MainBundle\Entity\Site',
                'property' => 'name',
            )
        );
    }

    private function getContentTemplate()
    {
        /** @var $post \Lacus\MainBundle\Entity\Post */
        $post = $this->getSubject();
        $providerName = $post->getMapper()->getProvider();
        $provider = $this->providerPool->getProvider($providerName);

        return $provider->getContentTemplate();
    }

    public function isGranted($name, $object = null)
    {
        if ($name === 'CREATE') {
            return false;
        }

        return parent::isGranted($name, $object);
    }

    public function hasAccessToAnyProvider()
    {
        $hasAccess = false;
        foreach ($this->providerPool->getProviderAliases() as $provider) {
            if ($this->isGranted(sprintf('PROVIDER_%s', strtoupper($provider)))) {
                $hasAccess = true;
            }
        }

        return $hasAccess;
    }

    public function getSecurityInformation()
    {
        $providerAliases = $this->providerPool->getProviderAliases();
        $securityInformation = array();
        foreach ($providerAliases as $providerAlias) {
            $securityInformation[sprintf('PROVIDER_%s', strtoupper($providerAlias))] = array();
        }

        return parent::getSecurityInformation() + $securityInformation;
    }

    public function hasAccessToProvider($provider)
    {
        return $this->isGranted(sprintf('PROVIDER_%s', strtoupper($provider)));
    }

}