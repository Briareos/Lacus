<?php

namespace Lacus\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Doctrine\ORM\Query;
use Symfony\Component\Security\Core\SecurityContextInterface;
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

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    public function setProviderPool(ProviderPool $providerPool)
    {
        $this->providerPool = $providerPool;
    }

    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
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
        $collection->add(
            'set_status',
            '{post}/status/{status}/{token}',
            array(
                '_controller' => 'MainBundle:PostAdmin:setStatus',
            ),
            array(
                'status' => '(' . implode('|', array(Post::STATUS_DRAFT, Post::STATUS_REVIEW, Post::STATUS_QUEUE, Post::STATUS_PUBLISH, Post::STATUS_ARCHIVE, Post::STATUS_FAILURE)) . ')',
            )
        );
        $collection->add(
            'get_logs',
            '{id}/get-logs',
            array(
                '_controller' => 'MainBundle:PostAdmin:getLogs',
            )
        );
        $collection->add(
            'log_iframe',
            'log/{logId}/iframe',
            array(
                '_controller' => 'MainBundle:PostAdmin:logIframe',
            )
        );
    }

    public function getFormBuilder()
    {
        $formBuilder = parent::getFormBuilder();
        $formBuilder->addEventSubscriber(new GeneratePostForm($formBuilder->getFormFactory(), $this->getContentTemplate(), $this));

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
                'mapper',
                null,
                array(
                    'associated_tostring' => 'getName',
                )
            )
            ->add(
                'mapper.site',
                null,
                array(
                    'associated_tostring' => 'getName',
                )
            )
            ->add(
                'mapper.provider'
                ,
                null,
                array(
                    'label' => 'Provider',
                    'template' => 'MainBundle:PostAdmin:list_provider.html.twig',
                )
            )
            ->add(
                'status',
                null,
                array(
                    'label' => 'Status',
                    'template' => 'MainBundle:PostAdmin:list_status.html.twig',
                )
            )
            ->add(
                'logs',
                null,
                array(
                    'label' => 'Logs',
                    'template' => 'MainBundle:PostAdmin:list_logs.html.twig',
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
        /** @var $object Post */
        if ($name === 'EDIT' || $name === 'VIEW' || $name === 'DELETE' || $name === 'MASTER' || $name === 'PUBLISH') {
            if (parent::isGranted(sprintf('%s_ALL', $name))) {
                return true;
            }
            if ($object === null) {
                return false;
            }
            if ($this->userOwnsPost($object)) {
                return parent::isGranted(sprintf('%s_OWN', $name), $object);
            } else {
                return false;
            }
        } elseif ($name === 'LIST') {
            return parent::isGranted('LIST_OWN') || parent::isGranted('LIST_ALL');
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
        $securityInformation = array(
            'EDIT_OWN' => array(),
            'EDIT_ALL' => array(),
            'LIST_OWN' => array(),
            'LIST_ALL' => array(),
            'VIEW_OWN' => array(),
            'VIEW_ALL' => array(),
            'DELETE_OWN' => array(),
            'DELETE_ALL' => array(),
            'MASTER_OWN' => array(),
            'MASTER_ALL' => array(),
            'PUBLISH_OWN' => array(),
            'PUBLISH_ALL' => array(),
        );
        foreach ($providerAliases as $providerAlias) {
            $securityInformation[sprintf('PROVIDER_%s', strtoupper($providerAlias))] = array();
        }

        return $securityInformation;
    }

    public function hasAccessToProvider($provider)
    {
        return $this->isGranted(sprintf('PROVIDER_%s', strtoupper($provider)));
    }

    private function userOwnsPost(Post $post)
    {
        $user = $this->getUser();

        if ($user === null) {
            return false;
        }

        $owners = $post->getMapper()->getSite()->getUsers();
        foreach ($owners as $owner) {
            if ($owner->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }

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

    public function canSetStatus(Post $post, $status)
    {
        $currentStatus = $post->getStatus();

        $publishStatuses = array(Post::STATUS_QUEUE, Post::STATUS_PUBLISH);
        $automatedStatuses = array(Post::STATUS_ARCHIVE, Post::STATUS_FAILURE);

        if ($currentStatus === $status) {
            return true;
        }

        if ($currentStatus === Post::STATUS_ARCHIVE && $status !== Post::STATUS_FAILURE) {
            return false;
        }

        if (!$post->getId() && in_array($status, $automatedStatuses)) {
            return false;
        }

        if ($currentStatus === Post::STATUS_PUBLISH && !in_array($status, $automatedStatuses)) {
            return false;
        }

        if (in_array($status, $publishStatuses) || in_array($status, $automatedStatuses) || in_array($currentStatus, $publishStatuses) || in_array($currentStatus, $automatedStatuses)) {
            return $this->isGranted('PUBLISH', $post);
        }

        return $this->isGranted('EDIT', $post);
    }

    public function getPostCountByStatus($status)
    {
        /** @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $this->createQuery('list');
        $qb->select('COUNT(o.id) AS cnt');
        $qb->andWhere('o.status = :status');
        $qb->setParameter('status', $status);

        return $qb->getQuery()->execute(null, Query::HYDRATE_SINGLE_SCALAR);
    }

    public function createQuery($context = 'list')
    {
        /** @var $query \Doctrine\ORM\QueryBuilder */
        $query = $this->getModelManager()->createQuery($this->getClass());
        if (!$this->isGranted('LIST_ALL') && $this->isGranted('LIST_OWN')) {
            $query->innerJoin('o.mapper', 'm');
            $query->innerJoin('m.site', 's');
            $query->andWhere(':user Member Of s.users');
            $query->setParameter('user', $this->getUser());
        }

        foreach ($this->extensions as $extension) {
            /** @var $extension \Sonata\AdminBundle\Admin\AdminExtensionInterface */
            $extension->configureQuery($this, $query, $context);
        }

        return $query;
    }

    public function toString($object)
    {
        if ($object instanceof Post) {
            return $object->getId();
        } else {
            return 'Post';
        }
    }


}
