<?php

namespace Nz\CrawlerBundle\Model;

/**
 * @author nz
 */
interface ProfileInterface
{

    /**
     * Returns the id.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Get Name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set Name.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Get config.
     *
     * @return string
     */
    public function getConfig();

    /**
     * Set Config.
     *
     * @param string $config
     */
    public function setConfig($config);

    /**
     * Get processed.
     *
     * @return bool $processed
     */
    public function getProcessed();

    /**
     * Set processed.
     *
     * @param bool $processed
     */
    public function setProcessed($processed);

    /**
     * Get enabled.
     *
     * @return bool $enabled
     */
    public function getEnabled();

    /**
     * Set enabled.
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * Get lastProcessedAt
     * 
     * @return \DateTime $lastProcessedAt
     */
    public function getLastProcessedAt();

    /**
     * Set lastProcessedAt
     * 
     * @param string $lastProcessedAt
     */
    public function setLastProcessedAt(\DateTime $lastProcessedAt = null);

    /**
     * Get lastProcessedStatus
     * 
     * @return string $lastProcessedStatus
     */
    public function getLastProcessedStatus();

    /**
     * Set lastProcessedStatus
     * 
     * @param string $lastProcessedStatus
     */
    public function setLastProcessedStatus($lastProcessedStatus = null);
}
