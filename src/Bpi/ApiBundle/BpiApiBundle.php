<?php

namespace Bpi\ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Bpi\ApiBundle\DependencyInjection\Security\Factory\PKFactory;

class BpiApiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new PKFactory());
    }
}
