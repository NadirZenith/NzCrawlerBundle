<?php

namespace Nz\CrawlerBundle\Tests\Client;

use Symfony\Component\DomCrawler\Crawler;

/**
 * test class
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{

    private function getLink()
    {
        $link = new LinkModel_Test();
        $link->setUrl('http://www.nzlab.es/test-url');

        return $link;
    }

    private function getMockCrawler($content)
    {
        return $this->getMockBuilder('Symfony\Component\DomCrawler\Crawler')
                ->setConstructorArgs(array($content))
                /* ->setConstructorArgs(array(file_get_contents(__DIR__ . '/../data/index_example.html'), 'http://example.com/', 'http://example.com/')) */
                ->setMethods(null)
                ->getMock();
    }

    private function getMockClient()
    {
        $mediaMatcher = $this->getMockBuilder('Nz\CrawlerBundle\Crawler\MediaMatcher')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $client = $this->getMockBuilder('Nz\CrawlerBundle\Tests\Client\ExampleClient')
            ->setConstructorArgs(array('client-name', $mediaMatcher))
            ->setMethods(array('crawl'))
            ->getMock();


        $client->configure($this->getLink());

        return $client;
    }

    public function testClient()
    {
        $client = $this->getMockClient();

        $this->assertEquals($client->getName(), 'client-name');

        $items = array('key' => 'val');
        $client->setItems($items);

        $this->assertEquals($client->getItems(), $items);
        return $client;
    }

    /**
     * @depends testClient
     */
    public function testIndex($client)
    {
        $crawler = $this->getMockCrawler(file_get_contents(__DIR__ . '/../data/index_example.html'));
        $client->expects($this->at(0))->method("crawl")->will($this->returnValue($crawler));
        $crawler = $this->getMockCrawler(file_get_contents(__DIR__ . '/../data/index_example_page_2.html'));
        $client->expects($this->at(1))->method("crawl")->will($this->returnValue($crawler));


        $this->assertEquals($client->getCurrentPage(), 1);
        $this->assertEquals($client->getNextPageUrl(1), 'http://www.example.com/page/1');
        $this->assertEquals($client->getNextPageUrl(2), 'http://www.example.com/page/2');

        /*include 'nzdebug.php';*/
        $urls = $client->getIndexUrls();
        /*d($urls);*/
        $this->assertNotEmpty($urls);
        $this->assertEquals($urls, array(
            'http://www.example.com/link1' => 'Title 1',
            'http://www.example.com/link2' => 'Title 2',
            'http://www.example.com/link3' => 'Title 3',
            'http://www.example.com/link4' => 'Title 4',
            'http://www.example.com/link5' => 'Title 5'
        ));

        /* $this->assertFalse($client->getUrls()); */

        return $client;
    }

    /**
     */
    public function testEntity()
    {
        $crawler = $this->getMockCrawler(file_get_contents(__DIR__ . '/../data/entity_example.html'));
        $client = $this->getMockClient();
        $client->expects($this->once())->method("crawl")->will($this->returnValue($crawler));

        $entity = $client->crawlToEntity(new ExampleEntity());

        /* var_dump($client->getItems());die(); */
        $this->assertEquals($client->getItems(), [
            'title' => 'Entity Title',
            'content' => [
                'lipsum 1',
                'lipsum 2',
                'lipsum 3',
            ],
            'medias' => [
                [
                    'url' => '/src/img1.jpg',
                    'provider' => 'sonata.media.provider.image'
                ],
                [
                    'url' => '/src/img2.jpg',
                    'provider' => 'sonata.media.provider.image'
                ],
                [
                    'url' => 'https://www.youtube.com/embed/sgzA6Up0m6w',
                    'id' => 'sgzA6Up0m6w',
                    'provider' => 'sonata.media.provider.youtube'
                ]
            ]
        ]);

        $this->assertEquals($entity->getTitle(), 'Entity Title');
        $this->assertEquals($entity->getContent(), 'lipsum 1,lipsum 2,lipsum 3');
    }
}
