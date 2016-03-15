<?php

namespace Nz\CrawlerBundle\Client;

use Nz\CrawlerBundle\Model\LinkInterface;

class ClientPool
{

    protected $index_clients;
    protected $entity_clients;

    public function __construct()
    {
        $this->index_clients = array();
        $this->entity_clients = array();
    }

    /**
     *  Add index client to pool
     * 
     * @param \Nz\CrawlerBundle\Client\IndexClientInterface $client Index Client class
     */
    public function addIndexClient(IndexClientInterface $client)
    {
        $this->index_clients[] = $client;
    }

    /**
     *  Get all index clients
     * 
     * @return array Array of index clients
     */
    public function getIndexClients()
    {
        return $this->index_clients;
    }

    /**
     * Add entity client to pool
     * 
     * @param \Nz\CrawlerBundle\Client\EntityClientInterface $client Entity Client class
     */
    public function addEntityClient(EntityClientInterface $client)
    {
        $this->entity_clients[$client->getHost()] = $client;
    }

    /**
     * @param LinkInterface $link Link object
     * 
     * @return EntityClientInterface | false EntityClient for provided link of false on no match
     */
    public function getEntityClientForLink(LinkInterface $link)
    {
        $client = $this->matchEntityClient($link);

        if (!$client) {
            return false;
        }

        $client->setLink($link);

        return $client;
    }

    /**
     * 
     * @param LinkInterface $link
     * @return EntityClientInterface
     */
    protected function matchEntityClient(LinkInterface $link)
    {
        $domain = parse_url($link->getUrl(), PHP_URL_HOST);

        $host = str_replace('www.', '', $domain);
        return isset($this->entity_clients[$host]) ? $this->entity_clients[$host] : false;
    }
}
