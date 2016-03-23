<?php

namespace Nz\CrawlerBundle\Client;

use Nz\CrawlerBundle\Model\LinkInterface;
use Nz\CrawlerBundle\Crawler\MediaMatcher;

interface ClientInterface
{

    /**
     * Init the client 
     * 
     * @param string $name 
     */
    public function __construct($name, MediaMatcher $mediaMatcher = null);

    /**
     * @param string $name Set client name
     */
    public function setName($name);

    /**
     * @return string Client name
     */
    public function getName();

    /**
     * @param array $config Configure client
     */
    public function configure(LinkInterface $link, array $config = array());

    /**
     * @param \Nz\CrawlerBundle\Model\LinkInterface $link
     */
    public function resetLink(LinkInterface $link);

    public function getIndexUrls();

    public function crawlToEntity($entity);

    /**
     * @return \Nz\CrawlerBundle\Model\LinkInterface
     * @throws \Nz\CrawlerBundle\Client\ClientException
     */
    public function getLink();
}
