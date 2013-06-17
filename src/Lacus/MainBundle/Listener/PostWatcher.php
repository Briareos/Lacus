<?php

namespace Lacus\MainBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Lacus\MainBundle\Post\PostPoster;
use Symfony\Component\DependencyInjection\ContainerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Doctrine\ORM\EntityManager;
use Lacus\MainBundle\Entity\Log;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Lacus\MainBundle\Entity\Post;

class PostWatcher
{
    private $postProducer;

    /**
     * @var ContainerInterface
     */
    private $container;

    private $publish = array();

    private $postPoster;

    function __construct(Producer $postProducer, ContainerInterface $container, PostPoster $postPoster)
    {
        $this->postProducer = $postProducer;
        $this->container = $container;
        $this->postPoster =$postPoster;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Post) {
            $this->createLog($args->getEntityManager(), $entity);
            $args->getEntityManager()->flush();

            if ($entity->getStatus() === Post::STATUS_PUBLISH) {
                $this->enqueue($entity);
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Post) {
            if ($args->hasChangedField('status')) {
                $this->createLog($args->getEntityManager(), $entity);
                if ($entity->getStatus() === Post::STATUS_PUBLISH) {
                    $this->publish[spl_object_hash($entity)] = true;
                }
            }
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Post) {
            // Since we can't flush for log entities inside preUpdate, we do it here.
            $args->getEntityManager()->flush();
            if (!empty($this->publish[spl_object_hash($entity)])) {
                $this->enqueue($entity);
            }
        }
    }

    public function createLog(EntityManager $em, Post $post)
    {
        $log = new Log();
        $log->setPost($post);
        $log->setStatus($post->getStatus());
        $log->setUser($this->getUser());
        if ($post->getLastMessage() !== null) {
            $log->setMessage($post->getLastMessage());
        }
        if ($post->getLastResponse() !== null) {
            $log->setResponse($post->getLastResponse()->getContent());
        }
        $em->persist($log);
    }

    public function enqueue(Post $post)
    {
//        $this->postPoster->post($post);
//        return;
        $msg = array('post' => $post->getId());
        $this->postProducer->publish(serialize($msg));
    }

    public function getUser()
    {
        /** @var $token \Symfony\Component\Security\Core\Authentication\Token\TokenInterface */
        if (null === $token = $this->container->get('security.context')->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}
