<?php

namespace Nz\CrawlerBundle\Entity;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Nz\CrawlerBundle\Model\ProfileManagerInterface;
use Nz\CrawlerBundle\Client\ClientPool;
use Nz\CrawlerBundle\Crawler\HandlerInterface;
use Nz\CrawlerBundle\Model\LinkInterface;
use Nz\CrawlerBundle\Model\ProfileInterface;

class ProfileManager extends BaseEntityManager implements ProfileManagerInterface
{

    protected $clientPool;
    protected $handler;

    public function handleProfileIndex($profile_id, $persist = false)
    {
        $profile = $this->getRepository()->find($profile_id);
        $client = $this->getClientPool()->getClient('config');

        $client->configure(new Link(), $profile->getParsedConfig());

        $links = $this->getHandler()->handleIndex($client, $persist);

        if (!empty($links) && $persist) {
            $profile->setLinks($links);
            $this->getObjectManager()->flush();
        }

        return $links;
    }

    public function handleProfileLinks(ProfileInterface $profile, $persist = false)
    {
        $links = $this->getLinkManager()->getRepository()->findProfileLinksForProcess($profile->getId(), 5);
        $client = $this->getClientPool()->getClient('config');
        $client->configure(new Link(), $profile->getParsedConfig());

        $entities = $this->getHandler()->handleLinks($client, $links, $persist);
        if (!empty($entities) && $persist) {
            $this->getObjectManager()->flush();
        }

        return $entities;
    }

    public function setClientPool(ClientPool$pool)
    {

        $this->clientPool = $pool;
    }

    protected function getClientPool()
    {

        return $this->clientPool;
    }

    public function setHandler(HandlerInterface $handler)
    {

        $this->handler = $handler;
    }

    protected function getHandler()
    {

        return $this->handler;
    }

    public function getLastHandlerErrors()
    {
        return $this->getHandler()->getErrors();
    }

    public function setLinkManager($linkManager)
    {

        $this->linkManager = $linkManager;
    }

    protected function getLinkManager()
    {

        return $this->linkManager;
    }
}
