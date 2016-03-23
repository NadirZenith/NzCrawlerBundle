<?php

namespace Nz\CrawlerBundle\Tests\Crawler;

use Nz\CrawlerBundle\Crawler\Handler;
use Nz\CrawlerBundle\Entity\Link;
use Nz\CrawlerBundle\Tests\Client\ExampleClient;
use Symfony\Component\DomCrawler\Crawler;
use Nz\CrawlerBundle\Tests\Client\LinkModel_Test;

/**
 */
class HandlerTest extends \PHPUnit_Framework_TestCase
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
            /* ->setMethods(null) */
            ->setMethods(array('crawl'))
            /* ->setMethods(array('crawl', 'afterEntityPersist')) */
            ->getMock();


        $client->configure($this->getLink());

        return $client;
    }

    private function getMockHandler()
    {
        //EntityRepository
        $er = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        //EntityManager
        $em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->method("isOpen")->will($this->returnValue(true));
        $em->method("getRepository")->will($this->returnValue($er));

        //ManagerRegistry
        $mr = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            /* ->setMethods(['getManager']) */
            ->getMock();
        $mr->method("getManager")->will($this->returnValue($em));


        $handler = $this->getMockBuilder('Nz\CrawlerBundle\Crawler\Handler')
            ->setConstructorArgs(array($mr))
            ->setMethods(null)
            ->getMock();

        return $handler;
    }

    public function testHandleIndex()
    {
        $handler = $this->getMockHandler();
        $client = $this->getMockClient();

        $crawler = $this->getMockCrawler(file_get_contents(__DIR__ . '/../data/index_example.html'));
        $client->expects($this->at(0))->method("crawl")->will($this->returnValue($crawler));
        $crawler = $this->getMockCrawler(file_get_contents(__DIR__ . '/../data/index_example_page_2.html'));
        $client->expects($this->at(1))->method("crawl")->will($this->returnValue($crawler));

        $links = $handler->handleIndex($client);

        $this->assertNotEmpty($links);
        $this->assertEquals(count($links), 5);

        return $links;
    }

    /**
     * @depends testHandleIndex
     */
    public function testHandleLinks($links)
    {
        $links = array_slice($links, -3);
        $handler = $this->getMockHandler();
        $client = $this->getMockClient();
        /*
          $client->expects($this->once())
          ->method('afterEntityPersist')
          ->with($this->equalTo('something'))
          ;
         */
        $crawler = $this->getMockCrawler(file_get_contents(__DIR__ . '/../data/entity_example.html'));
        $client->expects($this->at(0))->method("crawl")->will($this->returnValue($crawler));
        $crawler = $this->getMockCrawler(file_get_contents(__DIR__ . '/../data/entity_example_page_2.html'));
        $client->expects($this->at(1))->method("crawl")->will($this->returnValue($crawler));
        $crawler = $this->getMockCrawler('empty');
        $client->expects($this->at(2))->method("crawl")->will($this->returnValue($crawler));

        $entities = $handler->handleLinks($client, $links, true);
        $this->assertEquals(count($entities), 2);
        $this->assertEquals($entities[0]->getTitle(), 'Entity Title');
        $this->assertEquals($entities[0]->getContent(), 'lipsum 1,lipsum 2,lipsum 3');
        $this->assertEquals($entities[1]->getTitle(), 'Entity Title Second');
        $this->assertEquals($entities[1]->getContent(), 'lorem 1,lorem 2,lorem 3');
        $this->assertEquals(count($handler->getErrors()), 1);
        $this->assertEquals($links[0]->getNotes(), array(
            'crawled_entity' => 'crawled entity: Entity Title',
            'created_entity' => 'created entity: Nz\CrawlerBundle\Tests\Client\ExampleEntity:0'
        ));
        $this->assertEquals($links[2]->getNotes(), array('exception' => 'The current node list is empty.'));
    }
}
