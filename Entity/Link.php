<?php

namespace Nz\CrawlerBundle\Entity;

use Nz\CrawlerBundle\Model\Link as BaseLink;

/**
 * Description 
 *
 * @author nz
 */
class Link extends BaseLink
{

    /**
     * @var integer $id
     */
    protected $id;

    public function __construct()
    {

        $this->setCrawledAt(new \DateTime());
    }

    public function getId()
    {
        return $this->id;
    }
}
