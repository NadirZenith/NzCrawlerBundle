<?php

namespace Nz\CrawlerBundle\Entity;

use Nz\CrawlerBundle\Model\Profile as BaseProfile;

/**
 * Description 
 *
 * @author nz
 */
class Profile extends BaseProfile
{

    /**
     * @var integer $id
     */
    protected $id;

    public function __construct()
    {

    }

    public function getId()
    {
        return $this->id;
    }
}
