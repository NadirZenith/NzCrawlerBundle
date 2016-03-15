<?php

namespace Nz\CrawlerBundle\Crawler;

use Nz\CrawlerBundle\Client\IndexClientInterface;
use Nz\CrawlerBundle\Client\EntityClientInterface;

/**
 * Handler interface 
 * 
 * @author nz
 */
interface HandlerInterface
{

    /**
     * Handle Index Client
     * 
     * @param IndexClientInterface $client The client
     * @param boolean $persist If should persist Link
     * 
     * @return array Array of links
     */
    public function handleIndexClient(IndexClientInterface $client);

    /**
     * Handle Entity Client
     * 
     * @param EntityClientInterface $client
     * @param boolean $persist whether to persist entity
     * 
     * @return Entity | boolean Entity on success false otherwise
     */
    public function handleEntityClient(EntityClientInterface $client);
}
