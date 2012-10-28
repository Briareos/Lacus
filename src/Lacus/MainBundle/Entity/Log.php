<?php

namespace Lacus\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Lacus\MainBundle\Entity\Log
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Lacus\MainBundle\Entity\LogRepository")
 */
class Log
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var string $response
     *
     * @ORM\Column(name="response", type="text")
     */
    private $response;

    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="logs")
     * @ORM\JoinColumn(name="post", referencedColumnName="id", onDelete="SET NULL")
     */
    private $post;

    private $oldStatus;

    private $status;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Log
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set response
     *
     * @param string $response
     * @return Log
     */
    public function setResponse($response)
    {
        $this->response = $response;
    
        return $this;
    }

    /**
     * Get response
     *
     * @return string 
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set postData
     *
     * @param \stdClass $postData
     * @return Log
     */
    public function setPostData($postData)
    {
        $this->postData = $postData;
    
        return $this;
    }

    /**
     * Get postData
     *
     * @return \stdClass 
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * @return \Lacus\MainBundle\Entity\Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param \Lacus\MainBundle\Entity\Post $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}
