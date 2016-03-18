<?php

namespace Nz\CrawlerBundle\Client;

use Nz\CrawlerBundle\Model\LinkInterface;

interface BaseClientInterface
{

    /**
     * @param string $name Set client name
     */
    public function setName($name);

    /**
     * @return string Client name
     */
    public function getName();
}
