<?php

namespace Nz\CrawlerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCrawlCommand extends ContainerAwareCommand
{

    /**
     * Get Crawler handler
     * 
     * @return \Nz\CrawlerBundle\Crawler\Handler
     */
    protected function getHandler()
    {
        return $this->getContainer()->get('nz.crawler.handler');
    }

    /**
     * Get Link Manager
     * 
     * @return \Nz\CrawlerBundle\Client\ClientPool
     */
    protected function getClientPool()
    {
        return $this->getContainer()->get('nz.crawler.client.pool');
    }

    /**
     * Get Link Manager
     * 
     * @return \Nz\CrawlerBundle\Entity\LinkManager
     */
    protected function getLinkManager()
    {
        return $this->getContainer()->get('nz.crawler.link.manager');
    }
}
