<?php

namespace Nz\CrawlerBundle\Client;

use Symfony\Component\DomCrawler\Crawler;

abstract class BaseIndexClient extends BaseClient implements IndexClientInterface
{

    /**
     * The base url to crawl
     *
     * @var string
     */
    protected $baseurl;

    /**
     * The current page
     *
     * @var int
     */
    private $current_page = 1;

    /**
     * The page to start crawling
     *
     * @var int
     */
    protected $start_page = 1;

    /**
     * The maximum number of pages to crawl. 0 for all.
     *
     * @var int
     */
    protected $limit_pages = 1;

    /**
     * The css filter path for index links
     *
     * @var string
     */
    protected $index_link_filter;

    /**
     * Get Current page num
     *
     * @var int
     */
    public function getCurrentPage()
    {
        return $this->current_page;
    }

    /**
     * Get index urls
     * 
     * @return array | false array of urls or false if reached end of pages
     */
    public function getUrls()
    {
        if ($this->current_page > $this->limit_pages) {
            return false;
        }

        return $this->getNextIndexUrls();
    }

    /**
     * Get next index urls
     *
     * @return array Urls crawled
     */
    protected function getNextIndexUrls()
    {

        $nextUrl = $this->getNextPageUrl($this->start_page);
        $this->start_page ++;
        $this->current_page ++;

        $index_urls = $this->getBaseCrawler($nextUrl)->filter($this->index_link_filter);

        $urls = $this->filterUrls($this->getArrayAttributes($index_urls, 'href'));

        return $urls;
    }

    public function filterUrls($urls)
    {
        return $urls;
    }

    abstract public function getNextPageUrl($current_page);
}
