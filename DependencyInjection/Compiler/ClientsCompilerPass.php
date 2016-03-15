<?php

namespace Nz\CrawlerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class ClientsCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('nz.crawler.client.pool')) {
            return;
        }

        $definition = $container->getDefinition('nz.crawler.client.pool');

        foreach ($container->findTaggedServiceIds('nz.crawler') as $id => $attributes) {

            if ($attributes[0]['client_type'] === 'index') {

                $definition->addMethodCall('addIndexClient', array(new Reference($id)));
            } elseif ($attributes[0]['client_type'] === 'entity') {

                $definition->addMethodCall('addEntityClient', array(new Reference($id)));
            }
        }
    }
}
