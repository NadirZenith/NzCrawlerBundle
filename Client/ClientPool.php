<?php

namespace Nz\CrawlerBundle\Client;

use Nz\CrawlerBundle\Model\LinkInterface;

class ClientPool
{

    protected $clients = array();

    public function __construct()
    {
        $this->clients['index'] = array();
        $this->clients['entity'] = array();
    }

    /**
     *  Add index client to pool
     * 
     * @param \Nz\CrawlerBundle\Client\IndexClientInterface $client Index Client class
     */
    public function addIndexClient(IndexClientInterface $client)
    {
        $this->clients['index'][$client->getHost()] = $client;
    }

    /**
     *  Get all index clients
     * 
     * @return array Array of index clients
     */
    public function getIndexClients()
    {
        return $this->clients['index'];
    }

    /**
     * Add entity client to pool
     * 
     * @param \Nz\CrawlerBundle\Client\EntityClientInterface $client Entity Client class
     */
    public function addEntityClient(EntityClientInterface $client)
    {
        $this->clients['entity'][$client->getHost()] = $client;
    }

    /**
     * @param LinkInterface $link Link object
     * 
     * @return EntityClientInterface | false EntityClient for provided link of false on no match
     */
    public function getEntityClientForLink(LinkInterface $link)
    {
        $client = $this->getEntityClientForHost(str_replace('www.', '', parse_url($link->getUrl(), PHP_URL_HOST)));

        if (!$client) {
            return false;
        }

        $client->setLink($link);

        return $client;
    }

    /**
     * @param string $host host
     * 
     * @return EntityClientInterface | false EntityClient for provided link of false on no match
     */
    public function getIndexClientForHost($host)
    {
        return isset($this->clients['index'][$host]) ? $this->clients['index'][$host] : false;
    }
    /**
     * @param string $host host
     * 
     * @return EntityClientInterface | false EntityClient for provided link of false on no match
     */
    public function getEntityClientForHost($host)
    {
        return isset($this->clients['entity'][$host]) ? $this->clients['entity'][$host] : false;
    }
    /**
     * @param string $name
     * 
     * @return EntityClientInterface | false EntityClient for provided name of false on no match
     */
    public function getEntityClient($name)
    {
        return isset($this->clients['entity'][$name]) ? $this->clients['entity'][$name] : false;
    }
    
    /**
     * @param string $name
     * 
     * @return IndexClientInterface | false EntityClient for provided name of false on no match
     */
    public function getIndexClient($name)
    {
        return isset($this->clients['index'][$name]) ? $this->clients['index'][$name] : false;
    }
}
