<?php

namespace Nz\CrawlerBundle\Tests\Client;

use Nz\CrawlerBundle\Client\BaseClient;
use Symfony\Component\DomCrawler\Crawler;
use Nz\CrawlerBundle\Model\LinkInterface;

/**
 */
class ExampleClient extends BaseClient
{

    function configure(LinkInterface $link, array $config = array())
    {
        parent::configure($link, $config);
        $this->setTargetClass('Nz\CrawlerBundle\Tests\Client\ExampleEntity');
        $this->limit_pages = 2;
        $this->baseurl = 'http://www.example.com/';
        $this->index_link_filter = 'body main ul.index a';
        $this->next_page_selector = 'body main ul.pager a';
        $this->article_base_filter = 'body main';
    }

    public function getNextPageUrl($current_page)
    {
        $url = $this->baseurl . 'page/' . $current_page;
        return $url;
    }

    /**
     *  {@inheritdoc}
     */
    public function saveClientProfile(Crawler $entity_crawler)
    {
        // --- string
        $this->setItem('title', $entity_crawler->filter('h2')->text());

        // --- array
        $original = $this->getArrayValues($entity_crawler->filter('p'));
        $content = $this->filterContent($original);
        $this->setItem('content', $content);

        // --- medias
        // imgs
        $imgs = $this->getArrayAttributes($entity_crawler->filter('img'), 'src');
        $imgs = $this->filterContent($imgs);
        $imgs = $this->matchMediaProviders($imgs);

        // iframes
        $iframes = $this->getArrayAttributes($entity_crawler->filter('iframe'), 'src');
        $iframes = $this->filterContent($iframes);
        $iframes = $this->matchMediaProviders($iframes);
        $medias = array_merge($imgs, $iframes);
        $this->setItem('medias', $medias);
    }

    public function normalizeEntity($entity)
    {

        $entity->setTitle($this->getItem('title', true));
        $entity->setContent(implode(',', $this->getItem('content')));

        return $entity;
    }

    /**
     *  {@inheritdoc}
     */
    protected function stringsToFilter()
    {
        return [
            'remove this',
            'remove-this',
        ];
    }
}
