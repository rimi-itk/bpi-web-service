<?php

namespace Bpi\ApiBundle;

use Bpi\ApiBundle\DependencyInjection\Security\Factory\PKFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BpiApiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var \Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new PKFactory());
    }
}
