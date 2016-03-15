<?php

namespace Nz\CrawlerBundle\Tests\Model;

class ModelTest_Link extends \Nz\CrawlerBundle\Model\Link
{

    public function getId()
    {
        
    }
}

/**
 * Class LinkTest.
 *
 * Tests the link model
 */
class LinkTest extends \PHPUnit_Framework_TestCase
{

    public function testSettersGetters()
    {
        $date = new \DateTime();

        $link = new ModelTest_Link();
        $link->setUrl('www.sapo.pt');
        $link->setProcessed(true);
        $link->setHasError(true);
        $link->setSkip(true);
        $link->setNotes(['key' => 'value']);
        $link->setCrawledAt($date);

        $this->assertEquals($link->getUrl(), 'www.sapo.pt');
        $this->assertEquals($link->getProcessed(), true);
        $this->assertEquals($link->getHasError(), true);
        $this->assertEquals($link->getSkip(), true);
        $this->assertEquals($link->getNotes(), ['key' => 'value']);
        $this->assertEquals($link->getCrawledAt(), $date);
    }
}
