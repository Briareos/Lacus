<?php

namespace Lacus\MainBundle\Content;

use Lacus\MainBundle\Content\Provider\AbstractProvider;

class ProviderPool
{
    private $providers = array();

    public function addProvider(AbstractProvider $provider, $alias)
    {
        $this->providers[$alias] = $provider;
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function getProviderAliases()
    {
        return array_keys($this->providers);
    }

    /**
     * @param string $alias
     * @return AbstractProvider
     */
    public function getProvider($alias)
    {
        return $this->providers[$alias];
    }

    public function hasProvider($alias)
    {
        return isset($this->providers[$alias]);
    }
}