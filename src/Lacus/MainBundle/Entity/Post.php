<?php

namespace Lacus\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Sonata\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Lacus\MainBundle\Post\FieldNameConverterTrait;

/**
 * Lacus\MainBundle\Entity\Post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity(repositoryClass="Lacus\MainBundle\Entity\PostRepository")
 */
class Post
{
    use FieldNameConverterTrait;

    const STATUS_TEMPORARY = 'temporary';

    const STATUS_DRAFT = 'draft';

    const STATUS_REVIEW = 'review';

    const STATUS_QUEUE = 'queue';

    const STATUS_PUBLISH = 'publish';

    const STATUS_ARCHIVE = 'archive';

    const STATUS_FAILURE = 'failure';

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var array $content
     *
     * @ORM\Column(name="content", type="array", nullable=true)
     */
    private $content;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime $postedAt
     *
     * @ORM\Column(name="postedAt", type="datetime", nullable=true)
     */
    private $postedAt;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="File", mappedBy="post", cascade={"persist", "remove"}, indexBy="fieldName")
     */
    private $files;

    /**
     * @var Mapper
     *
     * @ORM\ManyToOne(targetEntity="Mapper", inversedBy="posts")
     * @ORM\JoinColumn(name="mapper", referencedColumnName="id", nullable=false)
     */
    private $mapper;

    /**
     * @var string
     *
     * @ORM\Column(name="uuid", type="string", length=255)
     */
    private $uuid;

    /**
     * @var string
     *
     * @ORM\Column(name="origin", type="text")
     */
    private $origin;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="posts")
     * @ORM\JoinColumn(name="account", referencedColumnName="id")
     */
    private $account;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Log", mappedBy="post")
     */
    private $logs;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Application\Sonata\UserBundle\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(name="user", referencedColumnName="id", onDelete="SET NULL")
     */
    private $user;


    function __construct(Mapper $mapper)
    {
        $this->content = array();
        $this->status = self::STATUS_TEMPORARY;
        $this->mapper = $mapper;
        $this->createdAt = new \DateTime();
        $this->files = new ArrayCollection();
        $this->logs = new ArrayCollection();
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
     * Set content
     *
     * @param array $content
     * @return Post
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return array
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Post
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
     * Set postedAt
     *
     * @param \DateTime $postedAt
     * @return Post
     */
    public function setPostedAt($postedAt)
    {
        $this->postedAt = $postedAt;

        return $this;
    }

    /**
     * Get postedAt
     *
     * @return \DateTime
     */
    public function getPostedAt()
    {
        return $this->postedAt;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function addFile(File $file)
    {
        $file->setPost($this);
        $this->files[$file->getFieldName()] = $file;
    }

    public function getPostFiles()
    {
        $files = array();
        foreach ($this->getFiles() as $file) {
            /** @var $file File */
            $files[$file->getFieldName()] = $file;
        }

        return $files;
    }

    public function setPostFiles(array $files)
    {
        $this->setFiles(new ArrayCollection());
        foreach ($files as $fieldName => $file) {
            /** @var $file File */
            $file->setFieldName($fieldName);
            $this->addFile($file);
        }
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return \Lacus\MainBundle\Entity\Mapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @param \Lacus\MainBundle\Entity\Mapper $mapper
     */
    public function setMapper($mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @return \Lacus\MainBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param \Lacus\MainBundle\Entity\Account $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $logs
     */
    public function setLogs($logs)
    {
        $this->logs = $logs;
    }

    /**
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \Application\Sonata\UserBundle\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getContentFields()
    {
        $contentFields = array();
        foreach ($this->content as $contentField => $contentValue) {
            $contentFields[$this->getRawFieldName($contentField)] = $contentValue;
        }

        return $contentFields;
    }

    public function getFileFields()
    {
        $fileFields = array();
        foreach ($this->getFiles() as $file) {
            /** @var $file File */
            $fileFields[$this->getRawFieldName($file->getFieldName())] = $file;
        }

        return $fileFields;
    }

}
