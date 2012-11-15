<?php

namespace Lacus\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Lacus\MainBundle\Entity\Mapper
 *
 * @ORM\Table(name="mapper")
 * @ORM\Entity(repositoryClass="Lacus\MainBundle\Entity\MapperRepository")
 */
class Mapper
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site", inversedBy="mappers")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $site;

    /**
     * @var string
     *
     * @ORM\Column(name="provider", type="string", length=255)
     */
    private $provider;

    /**
     * @var bool
     *
     * @ORM\Column(name="loginRequired", type="boolean")
     */
    private $loginRequired;

    /**
     * @var string
     *
     * @ORM\Column(name="submitUrl", type="string", length=255)
     */
    private $submitUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="submitButton", type="string", length=255)
     */
    private $submitButton;

    /**
     * @var string $data
     *
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    private $data;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Post", mappedBy="mapper")
     */
    private $posts;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="defaultForMappers")
     * @ORM\JoinColumn(name="defaultAccount", referencedColumnName="id", onDelete="SET NULL")
     */
    private $defaultAccount;

    /**
     * @var string
     *
     * @ORM\Column(name="successText", type="text", nullable=true)
     */
    private $successText;

    /**
     * @var string
     *
     * @ORM\Column(name="failureText", type="text", nullable=true)
     */
    private $failureText;


    public function __construct()
    {
        $this->posts = new ArrayCollection();
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

    public function getInfo()
    {
        $name = array();
        if ($this->getProvider()) {
            $name[] = ucfirst($this->getProvider());
        }
        if ($this->getSite()) {
            $name[] = $this->getSite()->getName();
        }
        return implode(' → ', $name);
    }

    public function getFullInfo()
    {
        return sprintf('%s: %s', $this->getName(), $this->getInfo());
    }

    /**
     * Set data
     *
     * @param string $data
     * @return Mapper
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return \Lacus\MainBundle\Entity\Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param \Lacus\MainBundle\Entity\Site $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->submitUrl;
    }

    /**
     * @param string $submitUrl
     */
    public function setSubmitUrl($submitUrl)
    {
        $this->submitUrl = $submitUrl;
    }

    /**
     * @return string
     */
    public function getSubmitButton()
    {
        return $this->submitButton;
    }

    /**
     * @param string $submitButton
     */
    public function setSubmitButton($submitButton)
    {
        $this->submitButton = $submitButton;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return boolean
     */
    public function getLoginRequired()
    {
        return $this->loginRequired;
    }

    /**
     * @param boolean $loginRequired
     */
    public function setLoginRequired($loginRequired)
    {
        $this->loginRequired = $loginRequired;
    }

    /**
     * @return \Lacus\MainBundle\Entity\Account
     */
    public function getDefaultAccount()
    {
        return $this->defaultAccount;
    }

    /**
     * @param \Lacus\MainBundle\Entity\Account $defaultAccount
     */
    public function setDefaultAccount($defaultAccount)
    {
        $this->defaultAccount = $defaultAccount;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $posts
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
    }

    public function isConfigured()
    {
        return $this->data !== null;
    }

    /**
     * @return string
     */
    public function getSuccessText()
    {
        return $this->successText;
    }

    /**
     * @param string $successText
     */
    public function setSuccessText($successText)
    {
        $this->successText = $successText;
    }

    /**
     * @return string
     */
    public function getFailureText()
    {
        return $this->failureText;
    }

    /**
     * @param string $failureText
     */
    public function setFailureText($failureText)
    {
        $this->failureText = $failureText;
    }
}
