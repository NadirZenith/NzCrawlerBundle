<?php

namespace Nz\CrawlerBundle\Tests\Crawler;

use Nz\CrawlerBundle\Crawler\Handler;
use Nz\CrawlerBundle\Client\BaseIndexClient;
use Nz\CrawlerBundle\Client\BaseEntityClient;
use Nz\CrawlerBundle\Entity\Link;
use Symfony\Component\DomCrawler\Crawler;

/**
 */
class HandlerTest extends \PHPUnit_Framework_TestCase
{

    private function getIndexClient()
    {
        return new TestIndexClient();
    }

    private function getEntityClient()
    {
        $entity_client = new TestEntityClient('nzlab.es');

        $link = new Link();
        $link->setUrl('http://www.nzlab.es/blog/use-composer-without-packagist');

        $entity_client->setLink($link);

        return $entity_client;
    }

    public function testHandleIndexClient()
    {
        $mr = $this->getMock('\Doctrine\Common\Persistence\ManagerRegistry');
        $index = $this->getIndexClient();
        $handler = new Handler($mr);


        $links = $handler->handleIndexClient($index);
        $this->assertNotEmpty($links);
    }

    public function testHandleEntityClient()
    {
        $mr = $this->getMock('\Doctrine\Common\Persistence\ManagerRegistry');
        $entity_client = $this->getEntityClient();
        $handler = new Handler($mr);
        $handler->setEntityClass(TestEntity::class);

        $entity = $handler->handleEntityClient($entity_client);
        $this->assertEquals($entity->getTitle(), 'Use composer without packagist');
        $this->assertEmpty($handler->getErrors());
        $this->assertTrue($entity_client->getLink()->getProcessed());
        $this->assertFalse($entity_client->getLink()->getHasError());
        $this->assertFalse($entity_client->getLink()->getSkip());
    }
}

/**
 * index client for test ok
 */
class TestIndexClient extends BaseIndexClient
{

    protected $start_page = 1;
    protected $limit_pages = 1;

    function __construct()
    {
        $this->baseurl = 'http://www.ainanas.com/';

        $this->index_link_filter = 'body div#content a.post_title';
    }

    public function getNextPageUrl($current_page)
    {
        $url = $this->baseurl . 'page/' . $current_page;
        return $url;
    }
}

/**
 * Entity client for test ok
 */
class TestEntityClient extends BaseEntityClient
{

    protected $article_base_filter = 'article.sonata-blog-post-container';

    /**
     *  {@inheritdoc}
     */
    public function saveClientProfile(Crawler $entity_crawler)
    {
        $this->setItem('title', trim($entity_crawler->filter('.sonata-blog-post-title a')->text()));
        $this->setItem('content', $this->getArrayValues($entity_crawler->filter('.sonata-blog-post-content p')), TRUE);
    }

    /**
     *  {@inheritdoc}
     */
    public function normalizeEntity($entity)
    {
        $entity->setTitle($this->getItem('title', true));
        $entity->setContent(implode('', $this->getItem('content')));

        return $entity;
    }

    /**
     *  {@inheritdoc}
     */
    protected function stringsToFilter()
    {
        return [
            'removethis',
        ];
    }
}

/**
 * entity for tests purposes
 */
class TestEntity
{

    protected $title;
    protected $content;

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }
}

/**
 * index client for test exceptions
 */
class ExceptionIndexClient extends BaseIndexClient
{

    protected $start_page = 1;
    protected $limit_pages = 1;

    function __construct()
    {
        $this->baseurl = 'http://www.ainanas.com/NOTFOUND/';

        $this->index_link_filter = 'body div#content a.post_title';
    }

    public function getNextPageUrl($current_page)
    {
        $url = $this->baseurl . 'page/' . $current_page;
        return $url;
    }
}
