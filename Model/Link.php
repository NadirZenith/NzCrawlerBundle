<?php

namespace Nz\CrawlerBundle\Model;

/**
 * @author nz
 */
abstract class Link implements LinkInterface
{

    protected $url;
    protected $name;
    protected $processed = false;
    protected $error = false;
    protected $skip = false;
    protected $notes;
    protected $items;
    protected $crawledAt;
    protected $profile;

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
    public function setUrl($url)
    {
        $this->url = $url;
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
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
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
    public function getError()
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function setError($error)
    {
        $this->error = $error;
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

        return $this;
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
    public function setItems(array $items = array())
    {
        $this->items = serialize($items);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        try {

            return unserialize($this->items);
        } catch (\Exception $ex) {
            $this->setNote('invalid_items', $ex);
            return [];
        }
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

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProfile(Profile $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProfile()
    {
        return $this->profile;
    }

    public function __toString()
    {
        return !empty($this->name) ? $this->name : $this->url;
    }
    /*
      public function validateItems(){
      d($this->url);
      d(unserialize($this->items));
      }
     */
}
