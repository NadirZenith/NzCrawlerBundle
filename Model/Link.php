<?php

namespace Nz\CrawlerBundle\Model;

/**
 * @author nz
 */
abstract class Link implements LinkInterface
{

    protected $url;
    protected $processed = false;
    protected $hasError = false;
    protected $skip = false;
    protected $notes;
    protected $crawledAt;

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        $domain = parse_url($this->getUrl(), PHP_URL_HOST);

        $host = str_replace('www.', '', $domain);

        return $host;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * {@inheritdoc}
     */
    public function getHasError()
    {
        return $this->hasError;
    }

    /**
     * {@inheritdoc}
     */
    public function setHasError($hasError)
    {
        $this->hasError = $hasError;
    }

    /**
     * {@inheritdoc}
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * {@inheritdoc}
     */
    public function setSkip($skip)
    {
        $this->skip = $skip;
    }

    /**
     * {@inheritdoc}
     */
    public function setNotes(array $notes = array())
    {
        $this->notes = $notes;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * {@inheritdoc}
     */
    public function setNote($name, $value)
    {
        $notes = $this->getNotes();

        $notes[$name] = $value;

        $this->setNotes($notes);
    }

    /**
     * {@inheritdoc}
     */
    public function getNote($name)
    {
        $notes = $this->getNotes();

        if (isset($notes[$name])) {
            return $notes[$name];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function removeNote($name)
    {
        $notes = $this->getNotes();

        if (isset($notes[$name])) {
            unset($notes[$name]);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCrawledAt()
    {
        return $this->crawledAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCrawledAt(\DateTime $crawledAt = null)
    {
        $this->crawledAt = $crawledAt;
    }

    public function __toString()
    {
        return $this->url;
    }
}
