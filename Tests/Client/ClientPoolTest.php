<?php

namespace Nz\CrawlerBundle\Tests\Client;

use Nz\CrawlerBundle\Client\ClientPool;
use Nz\CrawlerBundle\Model\Link;

class ModelTest_Link extends Link
{

    public function getId()
    {
        
    }
}

/**
 */
class ClientPoolTest extends \PHPUnit_Framework_TestCase
{

    public function getPool()
    {
        return new ClientPool();
    }

    public function testGetterSetters()
    {
        $pool = $this->getPool();

        $client1 = new TestIndexClient();
        $pool->addIndexClient($client1);

        $this->assertEquals($pool->getIndexClients(), [$client1]);

        $client2 = new TestEntityClient('nzlab.es');
        $pool->addEntityClient($client2);
        $link = new ModelTest_Link();
        $link->setUrl('http://www.nzlab.es/test-url');

        $this->assertEquals($pool->getEntityClientForLink($link), $client2);
        
        $link->setUrl('http://www.nzlab.com/test-url');
        $this->assertFalse($pool->getEntityClientForLink($link));
    }
}
