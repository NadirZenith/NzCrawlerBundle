<?php

namespace Nz\CrawlerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Nz\CrawlerBundle\DependencyInjection\Compiler\ClientsCompilerPass;

class NzCrawlerBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ClientsCompilerPass());
    }
}
