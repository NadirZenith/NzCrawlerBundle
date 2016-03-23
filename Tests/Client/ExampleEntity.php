<?php

namespace Nz\CrawlerBundle\Tests\Client;

class ExampleEntity
{

    protected $title;
    protected $content;

    public function getId()
    {
        return 0;
    }

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
