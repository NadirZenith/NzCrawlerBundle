<?php

namespace Nz\CrawlerBundle\Client;

use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client as GoutteClient;
use Nz\CrawlerBundle\Model\LinkInterface;
use Nz\CrawlerBundle\Crawler\MediaMatcher;

abstract class BaseClient implements ClientInterface
{

    /**
     * The client name
     *
     * @var string
     */
    private $name;

    /**
     * The client name
     *
     * @var string
     */
    private $host;

    /**
     * The array of crawled items
     *
     * @var array
     */
    private $profile_items;

    /**
     * @var \Nz\CrawlerBundle\Model\LinkInterface
     */
    private $link;

    /**
     * @var \Nz\CrawlerBundle\Crawler\MediaMatcher
     */
    private $mediaMatcher;

    /**
     * The target class entity
     *
     * @var string
     */
    private $target_class;

    /**
     * The css filter path for article
     *
     * @var string
     */
    protected $article_base_filter;

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
    protected $next_page_selector = false;

    /**
     * The next page link
     *
     * @var string
     */
    protected $next_page_link = false;
    protected $strings_to_filter = [];
    protected $regexes_to_filter = [];
    protected $medias = [];

    public function __construct($name, MediaMatcher $mediaMatcher = null)
    {
        $this->setName($name);
        $this->mediaMatcher = $mediaMatcher;
    }

    /**
     * @param string $name Set name
     * 
     * @return Client The client 
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get client name
     * 
     * @return string The client name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $host Set host
     * 
     * @return Client The client 
     */
    protected function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get client name
     * 
     * @return string The client name
     */
    protected function getHost()
    {
        return $this->host;
    }

    /**
     * Get Crawler for url
     * 
     * @param string $url The url to crawl
     * 
     * @return \Goutte\Client Crawler goute client
     * @throws \Nz\CrawlerBundle\Client\ClientException
     */
    protected function crawl($url)
    {
        $client = new GoutteClient();
        $crawler = $client->request('GET', $url);

        if (200 !== $client->getResponse()->getStatus()) {
            throw new ClientException(sprintf('Request for url: %s, returned with status code: %s', $url, $client->getResponse()->getStatus()));
        }

        return $crawler;
    }

    /**
     * Get Crawler for url
     * 
     * @param string $url The url to crawl
     * 
     * @return \Goutte\Client Crawler goute client
     */
    protected function getBaseCrawler($url)
    {
        /* d($url); */
        $crawler = $this->crawl($url);

        $parseurl = parse_url($url);
        $this->setHost(sprintf('%s://%s', $parseurl['scheme'], $parseurl['host']));

        return $crawler;
    }

    /**
     *  Get array of attributes from crawler node item
     * 
     * @param \Symfony\Component\DomCrawler\Crawler $crawler Crawler node
     * @param string $attr The attribute to get from the node
     *
     *  @return array The array of attributes from nodes
     */
    protected function getArrayAttributes(Crawler $crawler, $attr = 'src')
    {
        $items = [];
        foreach ($crawler as $item) {
            if ($item->hasAttribute($attr)) {
                $items[] = $item->getAttribute($attr);
            }
        }

        return $items;
    }

    /**
     *  Get array of values from crawler node item
     * 
     * @param \Symfony\Component\DomCrawler\Crawler $crawler Crawler node
     * @param string $value The value to get from the node
     *
     *  @return array The array of values from nodes
     */
    protected function getArrayValues(Crawler $crawler, $value = 'text')
    {

        $items = [];
        foreach ($crawler as $item) {
            $item = new Crawler($item);
            $r = trim($item->$value());

            if (!empty($r)) {
                $items[] = $r;
            }
        }

        return $items;
    }
    /////////////// utility functions to process crawled objects

    /**
     *  Filter array of content against stings and regexes
     * 
     *  @return array New content filtered 
     */
    protected function filterContent(array $content, $filter_keys = false)
    {
        $new_content = [];
        //['key' => 'val' ]
        //['val' ]
        foreach ($content as $key => $thing) {
            if (is_string($key)) {
                if (
                    $this->contains($key, $this->stringsToFilter()) ||
                    $this->contains($thing, $this->stringsToFilter())
                ) {
                    continue;
                }

                if (
                    $this->match($key, $this->regexesToFilter()) ||
                    $this->match($thing, $this->regexesToFilter())
                ) {
                    continue;
                }

                $new_content[$key] = $thing;
            } else {

                if ($this->contains($thing, $this->stringsToFilter())) {
                    continue;
                }
                if ($this->match($thing, $this->regexesToFilter())) {
                    continue;
                }
                $new_content[] = $thing;
            }
        }

        return $new_content;
    }

    /**
     *  Search for string in array
     *  
     * @return boolean Return true if string is in array false otherwise
     */
    private function contains($str, array $arr)
    {
        foreach ($arr as $a) {
            if (stripos($str, $a) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     *  Match string against array of regexes
     * 
     *  @return boolean Return true if string matches any of array regexes false otherwise
     */
    private function match($subject, array $arr)
    {
        foreach ($arr as $pattern) {
            if (preg_match($pattern, $subject)) {
                return true;
            }
        }

        return false;
    }

    /**
     *  Get strings to filter
     * 
     *  @return array Array of strings to filter
     */
    protected function stringsToFilter()
    {
        return $this->strings_to_filter;
    }

    /**
     *  Get regexes to filter
     * 
     *  @return array Array of regexes to filter
     */
    protected function regexesToFilter()
    {
        /* return []; */
        return $this->regexes_to_filter;
    }

    /**
     *  Truncate string to given length
     * 
     *  @param string  $string String to truncate
     *  @param integer $length Number of chars to truncate 
     *  @param string  $append String to append to truncated string
     */
    protected function truncate($string, $length = 100, $append = "&hellip;")
    {
        if (strlen($string) > $length) {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0] . $append;
        }

        return trim($string);
    }

    /**
     * Set profile item if not empty
     * 
     * @param string    $item   The array key to store profile item value
     * @param mixed     $value  The value for the profile item
     * @param boolean   $filter Whether to filter the value
     * 
     * @return mixed The item
     */
    protected function setItem($item, $value)
    {
        if (empty($value)) {
            return;
        }

        $this->profile_items[$item] = $value;

        return $this->profile_items[$item];
    }

    /**
     *  Get item from profile
     *  
     *  @param string   $item        Profile item name
     *  @param boolean  $required    If this item is required
     * 
     *  @throws \Nz\CrawlerBundle\Client\ClientException
     * 
     *  @return mixed Profile item if exist void otherwise
     */
    protected function getItem($item, $required = false)
    {
        if (isset($this->profile_items[$item])) {

            return $this->profile_items[$item];
        } else if ($required) {

            throw new ClientException(sprintf('Entity item not found: %s', $item));
        }

        return;
    }

    /**
     *  Get items from profile
     * 
     *  @return array Profile items
     */
    public function getItems()
    {
        return $this->profile_items;
    }

    /**
     *  Set items to profile
     * 
     *  @return object $this
     */
    public function setItems($items)
    {
        $this->profile_items = $items;

        return $this;
    }

    /**
     * @return \Nz\CrawlerBundle\Crawler\MediaMatcher
     */
    public function getMediaMatcher()
    {
        return $this->mediaMatcher;
    }

    /**
     *  {@inheritdoc}
     */
    public function matchMediaProviders(array $urls)
    {
        return $this->getMediaMatcher()->matchProviders($urls);
    }

    /**
     * @param \Nz\CrawlerBundle\Model\LinkInterface
     */
    public function resetLink(LinkInterface $link)
    {
        //reset items & medias
        $this->profile_items = [];
        $this->medias = [];
        $this->link = $link;
    }

    /**
     * @return \Nz\CrawlerBundle\Model\LinkInterface
     */
    public function getLink()
    {
        if (!$this->link) {
            /* throw new ClientException('This client has no Link'); */
        }
        return $this->link;
    }

    public function configure(LinkInterface $link, array $config = array())
    {

        $this->resetLink($link);
    }

    /**
     *  Receives a object entity
     * 
     * @param Object $entity Entity to crawl to
     */
    public function crawlToEntity($entity)
    {

        $entity_crawler = $this->getBaseCrawler($this->getLink()->getUrl())->filter($this->article_base_filter);


        $this->saveClientProfile($entity_crawler);

        return $this->normalizeEntity($entity);
    }

    /**
     * Called after entity persitence
     * 
     * @param object $entity The entity
     */
    public function afterEntityPersist($entity)
    {
        $this->getMediaMatcher()->cleanUp();
    }

    /**
     * Set target Class
     * 
     * @param string $class 
     */
    public function setTargetClass($class)
    {
        $this->target_class = $class;
    }

    /**
     * get target Class
     * 
     * @param string $class 
     */
    public function createEntity()
    {
        return new $this->target_class();
    }

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
    public function getIndexUrls()
    {
        $all = array();
        while ($urls = $this->getUrls()) {
            $all = array_merge($all, $urls);
        }

        return $all;
    }

    /**
     * Get next index urls
     * 
     * @return array | false array of urls or false if reached end of pages
     */
    protected function getUrls()
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
                    $this->next_page_link = rtrim($this->baseurl, '/') . $link;
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

    protected function filterUrls($urls)
    {
        $urls = $this->filterContent($urls);
        $new_urls = [];
        foreach ($urls as $url => $title) {
            //if relative url prepend domain
            $url = (FALSE === strpos($url, $this->getHost())) ?
                $url = rtrim($this->getHost(), '/') . $url :
                $url;

            $new_urls[$url] = $title;
        }
        return $new_urls;
    }

    /**
     * Get Next Page Url
     * 
     * @param integer $current_page Current index page count
     */
    abstract public function getNextPageUrl($current_page);

    /**
     * Saves the client profile from crawler
     * 
     * @param \Symfony\Component\DomCrawler\Crawler $entity_crawler The entity crawler
     */
    abstract protected function saveClientProfile(Crawler $entity_crawler);

    /**
     * Normalize entity from saved profile
     * 
     * @param object $entity The entity
     */
    abstract protected function normalizeEntity($entity);
}
