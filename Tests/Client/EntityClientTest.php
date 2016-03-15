<?php

namespace Nz\CrawlerBundle\Tests\Client;

use Nz\CrawlerBundle\Client\BaseEntityClient;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Entity client for test ok
 */
class TestEntityClient extends BaseEntityClient
{

    protected $article_base_filter = '#content';

    /**
     *  {@inheritdoc}
     */
    public function saveClientProfile(Crawler $entity_crawler)
    {
        $this->setItem('title', trim($entity_crawler->filter('.sonata-blog-post-title a')->text()));
        $this->setItem('content', $this->getArrayValues($entity_crawler->filter('.sonata-blog-post-content p')), TRUE);
        /* var_dump($this->getItem('content')); */
        /* die(); */
    }

    /**
     * Normalize clrawled profile to entity
     * 
     * @param object $entity The entity
     * 
     * @return object $entity The normalized entity
     */
    public function normalizeEntity($entity)
    {

        $entity->setTitle($this->getItem('title', true));
        $entity->setContent($this->getItem('content'));

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
 * Entity client for test exception
 */
class ExceptionEntityClient extends BaseEntityClient
{

    protected $article_base_filter = '#content';

    /**
     *  {@inheritdoc}
     */
    public function saveClientProfile(Crawler $entity_crawler)
    {
        $this->setItem('content', trim($entity_crawler->filter('.sonata-blog-post-title a')->text()));
    }

    /**
     * Normalize clrawled profile to entity
     * 
     * @param object $entity The entity
     * 
     * @return object $entity The normalized entity

     */
    public function normalizeEntity($entity)
    {

        $title = $this->getItem('title', true);
        $entity->setTitle($title);

        return $entity;
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
 * test class
 */
class EntityClientTest extends \PHPUnit_Framework_TestCase
{

    private function getClient()
    {
        $client = new TestEntityClient('nzlab.es');
        return $client;
    }

    public function testSaveClientProfile()
    {
        $client = $this->getClient();

        $client->saveClientProfile($this->getCrawler());
        $this->assertEquals($client->getItems(), [
            'title' => 'Use composer without packagist',
            'content' => [
                'this content stays',
                'this content too'
            ]
        ]);

        return $client;
    }

    /**
     * @depends testSaveClientProfile
     */
    public function testNormalizeEntity($client)
    {

        $entity = $client->normalizeEntity(new TestEntity());
        $this->assertEquals($entity->getTitle(), 'Use composer without packagist');
    }

    /**
     * @expectedException \Nz\CrawlerBundle\Client\ClientException
     */
    public function testNormalizeEntityException()
    {
        $client = new ExceptionEntityClient('nzlab.es');
        $client->normalizeEntity(new TestEntity());
    }

    private function getCrawler()
    {

        return new Crawler($this->getCrawlerContent());
    }

    private function getCrawlerContent()
    {
        return <<<CONTENT
<article class="sonata-blog-post-container">
    <header>

        <h1 class="sonata-blog-post-title">
            <a href="http://www.nzlab.es/blog/use-composer-without-packagist">Use composer without packagist</a>
            <span class="sonata-blog-post-author">by nz</span>
        </h1>

    </header>

    <div class="sonata-blog-post-content">

        <p>this content stays</p>
        <p>removethis</p>
        <p>this content too</p>

    </div>

    </article>
CONTENT;
    }
}
