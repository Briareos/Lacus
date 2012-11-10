<?php

namespace Lacus\MainBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Doctrine\ORM\EntityManager;
use Lacus\MainBundle\Entity\Log;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Lacus\MainBundle\Entity\Post;

class PostWatcher
{
    private $postPoster;

    function __construct(Producer $postPoster)
    {
        $this->postPoster = $postPoster;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Post) {
            $this->createLog($args->getEntityManager(), $entity);
            if ($entity->getStatus() === Post::STATUS_PUBLISH) {
                $this->enqueue($entity);
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Post && $entity->getStatus() === Post::STATUS_PUBLISH) {
            if ($args->hasChangedField('status')) {
                $this->createLog($args->getEntityManager(), $entity);
                if ($entity->getStatus() === Post::STATUS_PUBLISH) {
                    $this->enqueue($entity);
                }
            }
        }
    }

    public function createLog(EntityManager $em, Post $post)
    {
        $log = new Log();
        $log->setPost($post);
        $log->setStatus($post->getStatus());
    }

    public function enqueue(Post $post)
    {
        $msg = array('post' => $post->getId());
        $this->postPoster->publish(serialize($msg));
    }
}