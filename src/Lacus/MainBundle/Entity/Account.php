<?php

namespace Lacus\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Buzz\Util\CookieJar;

/**
 * Lacus\MainBundle\Entity\Account
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Lacus\MainBundle\Entity\AccountRepository")
 */
class Account
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
     * @ORM\Column(name="label", type="string", length=255)
     */
    private $label;

    /**
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site", inversedBy="accounts")
     * @ORM\JoinColumn(name="site", referencedColumnName="id")
     */
    private $site;

    /**
     * @var CookieJar
     *
     * @ORM\Column(type="object")
     */
    private $cookieJar;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Post", mappedBy="account")
     */
    private $posts;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Mapper", mappedBy="defaultAccount")
     */
    private $defaultForMappers;


    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->defaultForMappers = new ArrayCollection();
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
     * Set username
     *
     * @param string $username
     * @return Account
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Account
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
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
    public function setSite(Site $site)
    {
        $this->site = $site;
    }

    /**
     * @return CookieJar
     */
    public function getCookieJar()
    {
        return $this->cookieJar;
    }

    /**
     * @param CookieJar $cookieJar
     */
    public function setCookieJar(CookieJar $cookieJar)
    {
        $this->cookieJar = $cookieJar;
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

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDefaultForMappers()
    {
        return $this->defaultForMappers;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $defaultForMappers
     */
    public function setDefaultForMappers($defaultForMappers)
    {
        $this->defaultForMappers = $defaultForMappers;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function __toString()
    {
        if ($this->label === null) {
            return $this->username;
        }

        return $this->label;
    }
}
