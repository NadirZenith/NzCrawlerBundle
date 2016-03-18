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
     * The base domain
     *
     * @var string
     */
    protected $base_domain;

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
     * The css filter path for next page
     *
     * @var string
     */
    protected $next_page_selector;

    /**
     * The next page link
     *
     * @var string
     */
    protected $next_page_link;

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
        //use next page link if it exixt
        //fallback to get next page url
        $nextUrl = $this->next_page_link ? //
            $this->next_page_link : //
            $this->getNextPageUrl($this->start_page);

        $this->start_page ++;
        $this->current_page ++;
        $crawler = $this->getBaseCrawler($nextUrl);

        //if has next selector
        if ($this->next_page_selector) {
            //get next page link from current crawler
            $link = $crawler->filter($this->next_page_selector);
            if ($link->count() > 0) {
                $link = $link->attr('href');
                if (FALSE === strpos($link, $this->baseurl)) {
                    $this->next_page_link = $this->baseurl . $link;
                } else {
                    $this->next_page_link = $link;
                }
            }
        }

        $index_urls = $crawler->filter($this->index_link_filter);

        //build url => text array 
        $urls = [];
        foreach ($index_urls as $index_url) {
            $item = new Crawler($index_url);

            if ($index_url->hasAttribute('href')) {
                $urls[$index_url->getAttribute('href')] = trim($item->text());
            }
        }
        $urls = $this->filterUrls($urls);

        return $urls;
    }

    public function filterUrls($urls)
    {
        return $urls;
    }

    abstract public function getNextPageUrl($current_page);
}
