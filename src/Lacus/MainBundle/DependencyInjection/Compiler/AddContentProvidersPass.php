<?php

namespace Lacus\MainBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

/*
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AddContentProvidersPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('lacus.content_provider.pool');

        foreach ($container->findTaggedServiceIds('lacus.content_provider') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias']) ? $tag[0]['alias'] : $serviceId;
            $definition->addMethodCall('addProvider', array(new Reference($serviceId), $alias));
        }
    }
}
