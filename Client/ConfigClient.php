<?php

namespace Nz\CrawlerBundle\Client;

use Nz\CrawlerBundle\Entity\Profile;
use Symfony\Component\DomCrawler\Crawler;
use AppBundle\Entity\Media\Media;
use AppBundle\Entity\Media\Gallery;
use AppBundle\Entity\Media\GalleryHasMedia;
use Nz\CrawlerBundle\Model\LinkInterface;

class ConfigClient extends BaseClient
{

    protected $config = [];

    public function useProfile(Profile $profile)
    {
        $this->configure($profile->getParsedConfig());

        return $this;
    }

    public function configure(LinkInterface $link, array $config = array())
    {
        /* dd($config); */
        parent::configure($link, $config);
        extract($config);
        $this->config = $config;
        $this->setTargetClass($target_class);

        $this->strings_to_filter = $strings_to_filter;
        $this->regexes_to_filter = $regexes_to_filter;
        //
        $this->next_page_selector = $next_page_selector;
        $this->base_domain = $base_domain;
        $this->baseurl = $baseurl;
        $this->index_link_filter = $link_filter_selector;
        $this->start_page = $start_page;
        $this->limit_pages = $limit_pages;
        $this->next_page_mask = $next_page_mask;
        $this->article_base_filter = $config['article_base_filter'];
        $this->strings_to_filter = $config['strings_to_filter'];
        $this->regexes_to_filter = $config['regexes_to_filter'];
    }

    public function getNextPageUrl($current_page)
    {
        $url = str_replace('%baseurl%', $this->baseurl, $this->next_page_mask);
        $url = str_replace('%current_page%', $current_page, $url);

        return $url;
    }

    private function getCrawlerValue(Crawler $entity_crawler, $selector, $modifier = 'text', $options = array())
    {
        $value = $entity_crawler->filter($selector);

        if ($value->count() === 0) {
            /* return false; @note: medias return 0 */
        }

        switch ($modifier) {
            case "text":
                $result = $value->text();

                break;
            case "arrayValues":
                $result = $this->getArrayValues($value);

                break;
            case "arrayAttributes":
                $att = isset($options['att']) ? $options['att'] : 'src';
                $result = $this->getArrayAttributes($value, $att);

                break;
            case "stack":
                $result = [];
                foreach ($options as $stack) {
                    $result = array_merge($result, $this->getCrawlerValue($entity_crawler, $stack[0], $stack[1], $stack[2]));
                }

                break;
            default :
                /* $result = [] $this->modifierPool->modify($value, $modifier, $options); */

                break;
        }

        if (isset($options['filter']) && $options['filter']) {
            $result = $this->filterContent($result);
        }

        if (isset($options['matchMedias']) && $options['matchMedias']) {

            $providers = [];
            foreach ($this->matchMediaProviders($result) as $provider) {
                if ('sonata.media.provider.image' === $provider['provider']) {
                    if (FALSE === strpos($provider['url'], $this->getHost())) {
                        $provider['url'] = rtrim($this->getHost(), '/') . $provider['url'];
                    }
                }
                $providers[] = $provider;
            }
            $result = $providers;
        }

        if (isset($options['trim'])) {
            $result = trim($result);
        }

        return $result;
    }

    /**
     *  {@inheritdoc}
     */
    function applyItemFilters($value, $filter = 'text', $options = array())
    {

        $result = null;
        switch ($filter) {
            case'makeAbsolute':
                $result = array();
                foreach ($value as $a) {
                    if (FALSE === strpos($a, $this->getHost())) {
                        $result[] = rtrim($this->getHost(), '/') . $a;
                    } else {
                        $result[] = $a;
                    }
                }

                break;
            case'reorderMedias':

                $result = array_filter($value, [$this, 'reorderMedias']);

                break;
        }
        return $result;
    }

    /**
     *  {@inheritdoc}
     */
    function saveClientProfile(Crawler $entity_crawler)
    {
        if (!$entity_crawler->count()) {
            throw new \Nz\CrawlerBundle\Client\ClientException('Article base selector empty');
        }

        foreach ($this->config['items'] as $key => $item) {

            $value = $this->getCrawlerValue($entity_crawler, $item[0], $item[1], $item[2]);

            if (!$value) {

                $this->getLink()->setNote(sprintf('empty_field_%', $key), (string) 'none');
            } else {

                $this->setItem($key, $value);
            }
        }
        /* dd($this->getItems()); */
        /*
          foreach ($this->config['filters'] as $key => $item) {
          $this->setItem($item[0], $this->applyItemFilters($this->getItem($key), $item[1], $item[2]));
          }
         */

        return;
    }

    /**
     * Set entity defaults
     * 
     * @param object $entity The entity
     * 
     * @return object $entity The entity
     * 
     * */
    protected function setEntityDefaults($entity)
    {

        $entity->setSource($this->getLink()->getUrl());

        foreach ($this->config['defaults'] as $key => $default) {
            $setter = sprintf('set%s', ucfirst($key));

            $entity->$setter($default[0]);
        }

        return $entity;
    }

    private function getProfileItem($item, $modifier = 'text', $options = array())
    {
        $required = isset($options['required']) ? true : false;
        $value = $this->getItem($item, $required);

        switch ($modifier) {
            case "string":
                /* return $value; */

                break;
            case "slugify":
                $value = $this->slug($value);

                break;
            case "wrap":
                if (is_array($value)) {
                    $mask = isset($options['mask']) ? $options['mask'] : '<p>%s</p>';

                    $content = '';
                    foreach ($value as $val) {
                        $content .= sprintf($mask, $val);
                    }
                    $value = $content;
                }

                break;
            case "excerpt":
                if (is_array($value)) {
                    $length = isset($options['length']) ? (int) $options['length'] : 100;

                    $content = '';
                    foreach ($value as $val) {
                        $content .= $val;
                    }
                    $value = substr($content, 0, $length);
                }

                break;
            case "image":
                $medias = $this->normalizeMedias($value);
                /* d($medias); */
                $value = reset($medias);

                break;
            case "gallery":
                $name = isset($options['name']) ? $options['name'] : false;
                $medias = $this->normalizeMedias($value);
                array_shift($medias);
                $value = $this->normalizeGallery($medias);
                if ($value) {
                    $final_name = $name ? $this->getItem($name) : 'n/a';
                    $name = $this->getName();
                    $value->setName($final_name);
                }
                /* dd($value); */

                break;

            default :
                /* return $this->modifierPool->modify($value, $modifier, $options); */
                break;
        }
        return $value;
    }

    /**
     * Normalize clrawled profile to entity
     * 
     * @param object $entity The entity
     * 
     * @return object $entity The normalized entity

     */
    function normalizeEntity($entity)
    {
        $this->setEntityDefaults($entity);

        foreach ($this->config['entity'] as $key => $set) {
            $item = $this->getProfileItem($set[0], $set[1], $set[2]);
            $setter = sprintf('set%s', ucfirst($key));

            $entity->$setter($item);
        }
    }

    /**
     */
    protected function normalizeMedias(array $medias, $context = 'crawl')
    {
        if (!empty($this->medias)) {
            return $this->medias;
        }

        $this->medias = $this->getMediaMatcher()->normalizeMedias($medias, $context);

        return $this->medias;
    }

    /**
     */
    protected function normalizeGallery(array $medias, $context = 'crawl')
    {
        return $this->getMediaMatcher()->normalizeGallery($medias, $context);
    }
}
