<?php

namespace Nz\CrawlerBundle\Tests\Client;

use Nz\CrawlerBundle\Client\ClientPool;
use Nz\CrawlerBundle\Model\Link;



class ClientPoolTest extends \PHPUnit_Framework_TestCase
{

    public function getPool()
    {
        return new ClientPool();
    }

    public function testGetterSetters()
    {
        $pool = $this->getPool();
        /* $client1 = new ClientTest('client-1'); */
        $client1 = new ExampleClient('client-1');
        $client2 = new ExampleClient('client-2');
        $clientConf = new ExampleClient('config');

        $pool->addClient($client1);
        $pool->addClient($client2);
        $pool->addClient($clientConf);

        $this->assertEquals($pool->getClient('inexistent'), FALSE);
        $this->assertEquals($pool->getClient('client-1'), $client1);
        $this->assertEquals($pool->getClient('config'), $clientConf);
        $this->assertEquals($pool->getClients(), [$client1, $client2]);
        $this->assertEquals($pool->getClients(true), [$client1, $client2, $clientConf]);

        /*
          $client2 = new TestEntityClient('nzlab.es');
          $pool->addEntityClient($client2);
          $link = new ModelTest_Link();
          $link->setUrl('http://www.nzlab.es/test-url');

          $this->assertEquals($pool->getEntityClientForLink($link), $client2);

          $link->setUrl('http://www.nzlab.com/test-url');
          $this->assertFalse($pool->getEntityClientForLink($link));
         */
    }
}
