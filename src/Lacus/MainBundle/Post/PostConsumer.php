<?php

namespace Lacus\MainBundle\Post;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Lacus\MainBundle\Entity\Mapper;
use Buzz\Message\Response;
use Lacus\MainBundle\Post\Exception\SubmitFormNotFoundException;
use Lacus\MainBundle\Post\Exception\LoginFormNotFoundException;
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
        if($post->getStatus() !== Post::STATUS_PUBLISH) {
            // The status of this post was changed after it entered message queue, so just ignore it.
            return true;
        }
        try {
            $response = $this->poster->post($post);
            $post->setPostedAt(new \DateTime());
            $valid = $this->isResponseValid($response, $post->getMapper());
            if ($valid) {
                $post->setStatus(Post::STATUS_ARCHIVE);
            } else {
                $post->setStatus(Post::STATUS_FAILURE);
            }
            $post->setLastResponse($response);
            $this->em->persist($post);
            $this->em->flush();

            return true;
        } catch (LoginFormNotFoundException $le) {
            $error = 'Login form not found.';
        } catch (SubmitFormNotFoundException $se) {
            $error = 'Submit form not found.';
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        $post->setLastMessage($error);
        $post->setStatus(Post::STATUS_FAILURE);
        if (!empty($response)) {
            $post->setLastResponse($response);
        }
        $this->em->persist($post);
        $this->em->flush();

        return true;
    }

    private function isResponseValid(Response $response, Mapper $mapper)
    {
        if ($mapper->getSuccessText()) {
            return (strpos($response->getContent(), $mapper->getSuccessText()) !== false);
        }
        if ($mapper->getFailureText()) {
            return (strpos($response->getContent(), $mapper->getFailureText()) === false);
        }

        return true;
    }


}