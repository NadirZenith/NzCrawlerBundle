<?php

namespace Nz\CrawlerBundle\Client;

use Symfony\Component\DomCrawler\Crawler;
use Nz\CrawlerBundle\Model\LinkInterface;

abstract class BaseEntityClient extends BaseClient implements EntityClientInterface
{

    /**
     * The array of crawled items
     *
     * @var array
     */
    private $profile_items;

    /**
     * The link
     *
     * @var \Nz\CrawlerBundle\Model\LinkInterface
     */
    protected $link;

    /**
     * The css filter path for article
     *
     * @var string
     */
    protected $article_base_filter;

    /**
     * Set profile item if not empty
     * 
     * @param string    $item   The array key to store profile item value
     * @param mixed     $value  The value for the profile item
     * @param boolean   $filter Wether to filter the value
     * 
     * @return mixed The item
     */
    protected function setItem($item, $value, $filter = false)
    {
        if (empty($value)) {
            return;
        }

        $this->profile_items[$item] = $filter ? $this->filterContent($value) : $value;

        return $this->profile_items[$item];
    }

    /**
     *  Get item from profile
     *  
     *  @param string   $item        Profile item name
     *  @param boolean  $required    If this item is required
     * 
     *  @throws \Nz\CrawlerBundle\Client\ClientException
     * 
     *  @return mixed Profile item if exist void otherwise
     */
    protected function getItem($item, $required = false)
    {
        if (isset($this->profile_items[$item])) {

            return $this->profile_items[$item];
        } else if ($required) {

            throw new ClientException(sprintf('Entity item not found: %s', $item));
        }

        return;
    }

    /**
     *  Get items from profile
     * 
     *  @return array Profile items
     */
    public function getItems()
    {
        return $this->profile_items;
    }

    /**
     * @param \Nz\CrawlerBundle\Model\LinkInterface
     */
    public function setLink(LinkInterface $link)
    {
        $this->link = $link;
    }

    /**
     * @return \Nz\CrawlerBundle\Model\LinkInterface
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     *  Receives a object entity
     * 
     * @param Object $entity Entity to crawl to
     */
    public function crawlToEntity($entity)
    {

        $entity_crawler = $this->getBaseCrawler($this->link->getUrl())->filter($this->article_base_filter);

        $this->profile_items = [];
        $this->saveClientProfile($entity_crawler);

        return $this->normalizeEntity($entity);
    }

    /**
     * Called after entity persitence
     * 
     * @param object $entity The entity
     */
    public function afterEntityPersist($entity)
    {
        return;
    }

    /**
     * Saves the client profile from crawler
     * 
     * @param \Symfony\Component\DomCrawler\Crawler $entity_crawler The entity crawler
     */
    abstract protected function saveClientProfile(Crawler $entity_crawler);

    /**
     * Normalize entity from saved profile
     * 
     * @param object $entity The entity
     */
    abstract protected function normalizeEntity($entity);
}
