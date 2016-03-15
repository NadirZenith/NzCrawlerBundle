<?php

namespace Nz\CrawlerBundle\Model;

/**
 * @author nz
 */
abstract class Profile implements ProfileInterface
{

    protected $name;
    protected $config;
    protected $processed = false;
    protected $enabled = false;
    protected $skip = false;
    protected $lastProcessedAt;
    protected $lastProcessedStatus;

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->config;
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
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastProcessedAt(\DateTime $lastProcessedAt = null)
    {
        $this->lastProcessedAt = $lastProcessedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastProcessedAt()
    {
        return $this->lastProcessedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastProcessedStatus( $lastProcessedStatus = null)
    {
        $this->lastProcessedStatus = $lastProcessedStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastProcessedStatus()
    {
        return $this->lastProcessedStatus;
    }

    public function __toString()
    {
        return $this->name;
    }
}
