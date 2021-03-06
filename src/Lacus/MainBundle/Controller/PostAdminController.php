<?php

namespace Lacus\MainBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Lacus\MainBundle\Content\Exception\ValidationException;
use Lacus\MainBundle\Content\ContentCollection;
use Lacus\MainBundle\Entity\Mapper;
use Lacus\MainBundle\Entity\Post;
use Lacus\MainBundle\Form\Event\GeneratePostForm;
use Lacus\MainBundle\Form\Event\GenerateContentProviderForm;
use Lacus\MainBundle\Content\Provider\AbstractProvider;
use Lacus\MainBundle\Content\Content;
use JMS\DiExtraBundle\Annotation as DI;

class PostAdminController extends CRUDController
{
    /**
     * @DI\Inject("form.csrf_provider")
     *
     * @var \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface
     */
    private $csrfProvider;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     *
     * @DI\Inject("form.factory")
     */
    private $formFactory;

    /**
     * @DI\Inject("lacus.post.manager")
     *
     * @var \Lacus\MainBundle\Post\PostManager
     */
    private $postManager;

    /**
     * @DI\Inject("lacus.content_provider.pool")
     *
     * @var \Lacus\MainBundle\Content\ProviderPool
     */
    private $providerPool;

    /**
     * @DI\Inject("doctrine.orm.default_entity_manager")
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Lacus\MainBundle\Admin\PostAdmin
     */
    protected $admin;

    public function providerListAction()
    {
        if (!$this->admin->hasAccessToAnyProvider()) {
            throw new AccessDeniedException();
        }

        return $this->render(
            'MainBundle:PostAdmin:provider_list.html.twig',
            array(
                'action' => 'provider_list',
                'providers' => $this->providerPool->getProviderAliases(),
            )
        );
    }

    public function providerAction($provider)
    {
        if (!$this->admin->hasAccessToProvider($provider)) {
            throw new AccessDeniedException();
        }
        $activeProvider = $this->providerPool->getProvider($provider);

        $form = $this->getFilterForm($activeProvider);
        $form->bind($this->getRequest());

        $contentCollection = $activeProvider->getContentCollection();
        $sites = $this->getAvailableSites();

        $mapped_posts = $this->getMappedPosts($contentCollection);

        return $this->render(
            'MainBundle:PostAdmin:provider.html.twig',
            array(
                'action' => 'provider',
                'content_collection' => $contentCollection,
                'current_provider_url' => $activeProvider->getActiveUrl(),
                'current_provider_title' => $activeProvider->getActiveTitle(),
                'provider_content_width' => $activeProvider->getContentTemplate()->getWidth(),
                'form' => $form->createView(),
                'current_provider' => $provider,
                'providers' => $this->providerPool->getProviderAliases(),
                'sites' => $sites,
                'mapped_posts' => $mapped_posts,
            )
        );
    }

    public function getMappedPosts(ContentCollection $contentCollection)
    {
        /** @var $postRepository \Lacus\MainBundle\Entity\PostRepository */
        $postRepository = $this->getDoctrine()->getManager()->getRepository('MainBundle:Post');

        return $postRepository->fetchMappedPosts($contentCollection->getContentUuids());
    }

    public function providerFinalizeAction($provider, $mapperId)
    {
        if (!$this->admin->hasAccessToProvider($provider)) {
            throw new AccessDeniedException();
        }
        $contentSerialized = $this->getRequest()->request->get('content');
        $content = Content::getUnserialized($contentSerialized);

        if (!$content instanceof Content) {
            throw $this->createNotFoundException('Cannot unserialize content.');
        }

        if (!$this->providerPool->hasProvider($provider)) {
            throw $this->createNotFoundException('Invalid content provider specified.');
        }
        /** @var $mapper \Lacus\MainBundle\Entity\Mapper */
        $mapper = $this->getDoctrine()->getManager()->find('MainBundle:Mapper', $mapperId);
        if ($mapper === null) {
            throw $this->createNotFoundException('Mapper not found.');
        }

        if ($mapper->getData() === null) {
            throw $this->createNotFoundException('Mapper is not configured.');
        }

        $currentProvider = $this->providerPool->getProvider($provider);
        $currentProvider->getContent($content);

        $post = $this->getPost($mapper, $content);

        if (!$post->getId()) {
            $this->postManager->populatePostWithContentData($post, $content);
        }

        $form = $this->getContentForm($post, $content);

        return $this->render(
            'MainBundle:PostAdmin:provider_finalize.html.twig',
            array(
                'form' => $form->createView(),
                'post' => $post,
            )
        );
    }

    public function providerPostAction($mapperId)
    {
        /** @var $mapper \Lacus\MainBundle\Entity\Mapper */
        $mapper = $this->getDoctrine()->getManager()->find('MainBundle:Mapper', $mapperId);
        if ($mapper === null) {
            throw $this->createNotFoundException('Mapper not found.');
        }

        $contentSerialized = $this->getRequest()->request->get('finalize_form[_content]', null, true);
        $content = Content::getUnserialized($contentSerialized);
        if (!$content instanceof Content) {
            throw $this->createNotFoundException('Cannot unserialize content.');
        }

        $post = $this->getPost($mapper, $content);
        $form = $this->getContentForm($post, $content);

        $form->bind($this->getRequest());

        if (!$form->isValid()) {
            return $this->renderJson(
                array(
                    'status' => "KO",
                    'form' => $this->renderView(
                        'MainBundle:PostAdmin:provider_finalize.html.twig',
                        array(
                            'form' => $form->createView(),
                            'post' => $post,
                        )
                    ),
                )
            );
        } else {
            $this->getDoctrine()->getManager()->persist($post);
            $this->getDoctrine()->getManager()->flush();

            return $this->renderJson(
                array(
                    'status' => "OK",
                )
            );
        }
    }

    public function getPost(Mapper $mapper, Content $content)
    {
        /** @var $postRepository \Lacus\MainBundle\Entity\PostRepository */
        $postRepository = $this->em->getRepository('MainBundle:Post');
        $post = $postRepository->findOneBy(array('uuid' => $content->getUuid(), 'mapper' => $mapper));
        if ($post === null) {
            $post = new Post($mapper);
            $post->setUser($this->getUser());
            $post->setUuid($content->getUuid());
            $post->setOrigin($content->getUrl());
            if ($mapper->getDefaultAccount() !== null) {
                $post->setAccount($mapper->getDefaultAccount());
            }
        }

        return $post;
    }

    public function getAvailableSites()
    {
        /** @var $siteRepository \Lacus\MainBundle\Entity\SiteRepository */
        $siteRepository = $sites = $this->em->getRepository('MainBundle:Site');
        $sites = $siteRepository->findAll();

        return $sites;
    }

    protected function getFilterForm(AbstractProvider $provider)
    {
        /** @var $formBuilder \Symfony\Component\Form\FormBuilderInterface */
        $formBuilder = $this->formFactory->createNamedBuilder(
            '',
            'form',
            $provider,
            array(
                'csrf_protection' => false,
            )
        );
        $formBuilder->addEventSubscriber(new GenerateContentProviderForm($formBuilder->getFormFactory()));

        return $formBuilder->getForm();
    }

    protected function getContentForm(Post $post, Content $content)
    {
        $formBuilder = $this->formFactory->createNamedBuilder('finalize_form', 'form', $post);
        $formBuilder->addEventSubscriber(new GeneratePostForm($formBuilder->getFormFactory(), $content, $this->admin));
        $formBuilder->add(
            '_content',
            'hidden',
            array(
                'data' => $content->getSerialized(),
                'mapped' => false,
            )
        );

        return $formBuilder->getForm();
    }

    public function queueAction()
    {
        $query = $this->em->createQuery('Select p From MainBundle:Post p Order By p.createdAt Desc');

        $page = $this->getRequest()->query->getInt('page');
        if ($page === 0) {
            $page = 1;
        }

        /** @var $paginator \Knp\Component\Pager\Paginator */
        $paginator = $this->get('knp_paginator');
        $posts = $paginator->paginate($query, $page, 10);

        return $this->render(
            'MainBundle:PostAdmin:queue.html.twig',
            array(
                'posts' => $posts,
            )
        );
    }

    public function checkAction($provider)
    {
        $provider = $this->providerPool->getProvider($provider);

        try {
            $contentCollection = $provider->getContentCollection();
        } catch (ValidationException $ve) {

        } catch (\Exception $e) {

        }

        return $this->render(
            'MainBundle:PostAdmin:check.html.twig',
            array()
        );
    }

    public function checkProviderAction($provider)
    {

    }

    public function setStatusAction($post, $status, $token)
    {
        /** @var $post Post */
        $post = $this->em->find('MainBundle:Post', $post);
        if ($post === null) {
            throw $this->createNotFoundException('Post not found.');
        }

        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new HttpException(400, 'This method must be accessed through ajax.');
        }

        if ($status === $post->getStatus()
          || !$this->admin->canSetStatus($post, $status)
          || !$this->csrfProvider->isCsrfTokenValid('set-status', $token)
        ) {
            // Just ignore this request.
            return $this->renderJson(array());
        }

        $post->setStatus($status);
        $this->em->persist($post);
        $this->em->flush();

        return $this->renderJson(
            array(
                'status' => "OK",
                'statuses' => $this->renderView(
                    'MainBundle:PostAdmin:statuses.html.twig',
                    array(
                        'post' => $post,
                        'admin' => $this->admin,
                    )
                ),
            )
        );
    }

    public function getLogsAction($id)
    {
        /** @var $post Post */
        $post = $this->admin->getObject($id);

        if (!$post) {
            throw $this->createNotFoundException(sprintf('Unable to find the post with id : %s', $id));
        }

        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new HttpException(400, 'This method must be accessed through ajax.');
        }

        if (!$this->admin->isGranted('VIEW', $post)) {
            throw new AccessDeniedException();
        }

        return $this->renderJson(
            array(
                'status' => "OK",
                'logs' => $this->renderView(
                    'MainBundle:PostAdmin:logs.html.twig',
                    array(
                        'post' => $post,
                        'admin' => $this->admin,
                    )
                )
            )
        );
    }

    public function logIframeAction($logId)
    {
        /** @var $log \Lacus\MainBundle\Entity\Log */
        $log = $this->em->find('MainBundle:Log', $logId);

        if (!$log) {
            throw $this->createNotFoundException(sprintf('Unable to find the log with id : %s', $logId));
        }

        $post = $log->getPost();

        if (!$this->admin->isGranted('VIEW', $post)) {
            throw new AccessDeniedException();
        }

        $responseText = $log->getResponse();

        return new Response($responseText);
    }

}
