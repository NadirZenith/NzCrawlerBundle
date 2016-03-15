<?php

namespace Nz\CrawlerBundle\Entity;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Nz\CrawlerBundle\Model\LinkManagerInterface;

class LinkManager extends BaseEntityManager implements LinkManagerInterface
{

    /**
     * Find links for specific host
     * 
     * @param string        $host
     *
     * @return array        Links
     */
    public function findFromHost($host)
    {
        return $this->getRepository()->findFromHost($host);
    }

    /**
     * Find links for process
     * 
     * @param boolean $limit Limit of links to return
     * 
     * @param array Links
     */
    public function findLinksForProcess($limit = false)
    {
        return $this->getRepository()->findLinksForProcess($limit);
    }
}
