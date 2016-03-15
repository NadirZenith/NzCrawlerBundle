<?php

namespace Nz\CrawlerBundle\Client;

use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client as GoutteClient;

abstract class BaseClient implements BaseClientInterface
{

    /**
     * The host this client handles
     *
     * @var string
     */
    protected $host;

    public function __construct($host)
    {
        $this->setHost($host);
    }

    /**
     * @param string $host Set host this client handle
     * 
     * @return Client The client 
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get client host
     * 
     * @return string The client host url
     */
    public function getHost()
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
    protected function getBaseCrawler($url)
    {
        $client = new GoutteClient();

        $crawler = $client->request('GET', $url);

        $status_code = $client->getResponse()->getStatus();

        if (200 != $status_code) {
            throw new ClientException(sprintf('Request for url: %s, returned with status code: %s', $url, $status_code));
        }

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
     *  Search for string in array
     *  
     * @return boolean Return true if string is in array false otherwise
     */
    protected function contains($str, array $arr)
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
    protected function match($subject, array $arr)
    {
        foreach ($arr as $pattern) {
            if (preg_match($pattern, $subject)) {
                return true;
            }
        }

        return false;
    }

    /**
     *  Filter array of content against stings and regexes
     * 
     *  @return array New content filtered 
     */
    protected function filterContent(array $content)
    {

        $new_content = [];
        foreach ($content as $thing) {
            if ($this->contains($thing, $this->stringsToFilter())) {
                continue;
            }
            if ($this->match($thing, $this->regexesToFilter())) {
                continue;
            }

            $new_content[] = $thing;
        }

        return $new_content;
    }

    /**
     *  Get strings to filter
     * 
     *  @return array Array of strings to filter
     */
    protected function stringsToFilter()
    {
        return [];
    }

    /**
     *  Get regexes to filter
     * 
     *  @return array Array of regexes to filter
     */
    protected function regexesToFilter()
    {
        return [];
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
}
