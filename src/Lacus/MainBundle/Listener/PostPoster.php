<?php

namespace Lacus\MainBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Lacus\MainBundle\Post\Poster;
use Lacus\MainBundle\Entity\Post;

class PostPoster
{
    private $poster;

    function __construct(Poster $poster)
    {
        $this->poster = $poster;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Post && $entity->getStatus() === Post::STATUS_PUBLISH) {
            $this->executePoster($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Post && $entity->getStatus() === Post::STATUS_PUBLISH) {
            if ($args->getOldValue('status') !== Post::STATUS_PUBLISH) {
                $this->executePoster($entity);
            }
        }
    }

    public function executePoster(Post $post)
    {
        $this->poster->post($post);
    }
}