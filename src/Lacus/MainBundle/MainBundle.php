<?php

namespace Lacus\MainBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Lacus\MainBundle\DependencyInjection\Compiler\AddContentProvidersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MainBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddContentProvidersPass());
    }

}
