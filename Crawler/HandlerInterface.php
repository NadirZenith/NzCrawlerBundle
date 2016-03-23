<?php

namespace Nz\CrawlerBundle\Crawler;

use Nz\CrawlerBundle\Client\ClientInterface;

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
     * @param ClientInterface $client The client
     * @param boolean $persist If should persist Link
     * 
     * @return array Array of links
     */
    public function handleIndex(ClientInterface $client);

    /**
     * Handle Link
     * 
     * @param ClientInterface $client
     * @param boolean $persist whether to persist entity
     * 
     * @return Entity | boolean Entity on success false otherwise
     */
    public function handleLink(ClientInterface $client);
}
