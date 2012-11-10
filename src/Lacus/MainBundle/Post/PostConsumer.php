<?php

namespace Lacus\MainBundle\Post;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Lacus\MainBundle\Entity\Post;
use Doctrine\ORM\EntityManager;
use PhpAmqpLib\Message\AMQPMessage;
use Lacus\MainBundle\Post\PostPoster;

class PostConsumer implements ConsumerInterface
{
    /**
     * @var PostPoster
     */
    private $poster;

    private $em;

    function __construct(PostPoster $poster, EntityManager $em)
    {
        $this->poster = $poster;
        $this->em = $em;
    }

    function execute(AMQPMessage $msg)
    {
        $messageBody = unserialize($msg->body);
        $post = $this->em->find('MainBundle:Post', $messageBody['post']);
        if (!$post instanceof Post) {
            return false;
        }
        $response = $this->poster->post($post);
        return true;
    }

}