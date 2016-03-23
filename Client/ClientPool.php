<?php

namespace Nz\CrawlerBundle\Client;

use Nz\CrawlerBundle\Model\LinkInterface;

class ClientPool
{

    protected $clients;

    public function __construct()
    {
        $this->clients = array();
    }

    /**
     *  Add client to pool
     * 
     * @param \Nz\CrawlerBundle\Client\IndexClientInterface $client Index Client class
     */
    public function addClient(ClientInterface $client)
    {
        $this->clients[$client->getName()] = $client;
    }

    /**
     * @param string $name
     * 
     * @return ClientInterface | false 
     */
    public function getClient($name)
    {
        return isset($this->clients[$name]) ? $this->clients[$name] : false;
    }

    /**
     * 
     * @return array
     */
    public function getClients($force = false)
    {
        $clients = array_filter($this->clients, function($k, $v) use ($force) {
            return (!$force && $v === 'config') ? null : $k;
        }, ARRAY_FILTER_USE_BOTH);

        return array_values($clients);
    }

    /**
     * @param LinkInterface $link Link object
     * 
     * @return EntityClientInterface | false EntityClient for provided link of false on no match
     */
    public function getClientForLink(LinkInterface $link)
    {
        $client = $this->getClientForHost(str_replace('www.', '', parse_url($link->getUrl(), PHP_URL_HOST)));

        if (!$client) {
            return false;
        }

        $client->setLink($link);

        return $client;
    }

    /**
     * @param string $name host
     * 
     * @return EntityClientInterface | false EntityClient for provided link of false on no match
     */
    public function getClientForHost($name)
    {
        return isset($this->clients[$name]) ? $this->clients[$name] : false;
    }
}
