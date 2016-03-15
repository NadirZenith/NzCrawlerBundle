<?php

namespace Nz\CrawlerBundle\Client;

use Nz\CrawlerBundle\Model\LinkInterface;

interface EntityClientInterface extends BaseClientInterface
{

    /**
     * Set link
     * 
     * @param \Nz\CrawlerBundle\Model\LinkInterface $link link to handle
     */
    public function setLink(LinkInterface $link);

    /**
     * Get link
     * 
     * @return \Nz\CrawlerBundle\Model\LinkInterface Client link
     */
    public function getLink();

    /**
     *  Set up entity from crawl
     * 
     *  @param Object $entity Entity to crawl to
     * 
     *  @return Object Entity Ready to persist entity
     */
    public function crawlToEntity($entity);

    /**
     * @return void
     */
    public function afterEntityPersist($entity);
}
