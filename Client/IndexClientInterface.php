<?php

namespace Nz\CrawlerBundle\Client;

interface IndexClientInterface extends BaseClientInterface
{

    /**
     * Get next index page url
     * 
     * @param int $current_page Current page count
     * 
     * @return string The next page url
     */
    public function getNextPageUrl($current_page);

    /**
     * Get next index urls
     * 
     * @return array The next urls array
     */
    public function getUrls();

    /**
     * Filter urls
     * 
     * @param array $urls Filter index urls
     * 
     * @return array The filtered urls
     */
    public function filterUrls($urls);
}
