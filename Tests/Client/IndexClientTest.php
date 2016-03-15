<?php

namespace Nz\CrawlerBundle\Tests\Client;

use Nz\CrawlerBundle\Client\BaseIndexClient;

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

/**
 */
class IndexClientTest extends \PHPUnit_Framework_TestCase
{

    private function getClient()
    {
        $client = new TestIndexClient();
        return $client;
    }

    public function testCurrentPage()
    {
        $client = $this->getClient();

        $this->assertEquals($client->getCurrentPage(), 1);

        return $client;
    }

    /**
     * @depends testCurrentPage
     */
    public function testNextPageUrl($client)
    {

        $this->assertEquals($client->getNextPageUrl(1), 'http://www.ainanas.com/page/1');
    }

    /**
     * @depends testCurrentPage
     */
    public function testGetNextIndexUrls($client)
    {
        $urls = $client->getUrls();

        $this->assertNotEmpty($urls);
        $this->assertFalse($client->getUrls());
    }

    /**
     * @expectedException \Nz\CrawlerBundle\Client\ClientException
     */
    public function testCrawlerNotFoundException()
    {
        $client = new ExceptionIndexClient();

        $client->getUrls();
    }
}
