<?php

namespace Lacus\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Lacus\MainBundle\Entity\Site
 *
 * @ORM\Table(name="site")
 * @ORM\Entity(repositoryClass="Lacus\MainBundle\Entity\SiteRepository")
 */
class Site
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var string $url
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Account", mappedBy="site")
     */
    private $accounts;

    /**
     * @var string
     *
     * @ORM\Column(name="loginUrl", type="string", length=255, nullable=true)
     */
    private $loginUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="loginButton", type="string", length=255, nullable=true)
     */
    private $loginButton;

    /**
     * @var string
     *
     * @ORM\Column(name="loginUsername", type="string", length=255, nullable=true)
     */
    private $loginUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="loginPassword", type="string", length=255, nullable=true)
     */
    private $loginPassword;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Mapper", mappedBy="site")
     */
    private $mappers;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Application\Sonata\UserBundle\Entity\User", inversedBy="sites")
     * @ORM\JoinTable(name="site_user",
     *      joinColumns={@ORM\JoinColumn(name="site_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     */
    private $users;


    public function __construct()
    {
        $this->accounts = new ArrayCollection();
        $this->mappers = new ArrayCollection();
        $this->managers = new ArrayCollection();
        $this->active = false;
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
     * Set name
     *
     * @param string $name
     * @return Site
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Site
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $accounts
     */
    public function setAccounts($accounts)
    {
        $this->accounts = $accounts;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->loginUrl;
    }

    /**
     * @param string $loginUrl
     */
    public function setLoginUrl($loginUrl)
    {
        $this->loginUrl = $loginUrl;
    }

    /**
     * @return string
     */
    public function getLoginButton()
    {
        return $this->loginButton;
    }

    /**
     * @param string $loginButton
     */
    public function setLoginButton($loginButton)
    {
        $this->loginButton = $loginButton;
    }

    /**
     * @return string
     */
    public function getLoginPassword()
    {
        return $this->loginPassword;
    }

    /**
     * @param string $loginPassword
     */
    public function setLoginPassword($loginPassword)
    {
        $this->loginPassword = $loginPassword;
    }

    /**
     * @return string
     */
    public function getLoginUsername()
    {
        return $this->loginUsername;
    }

    /**
     * @param string $loginUsername
     */
    public function setLoginUsername($loginUsername)
    {
        $this->loginUsername = $loginUsername;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getMappers()
    {
        return $this->mappers;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $mappers
     */
    public function setMappers($mappers)
    {
        $this->mappers = $mappers;
    }

    public function getMappersFor($provider)
    {
        $mappers = array();
        foreach ($this->getMappers() as $mapper) {
            /** @var $mapper Mapper */
            if ($mapper->getProvider() === $provider) {
                $mappers[] = $mapper;
            }
        }

        return $mappers;
    }

    /**
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function setUsers($users)
    {
        $this->users = $users;
    }
}
