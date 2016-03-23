<?php

namespace Nz\CrawlerBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * @author nz
 */
abstract class Profile implements ProfileInterface
{

    protected $name;
    protected $config;
    protected $parsedConfig = false;
    protected $processed = false;
    protected $enabled = false;
    protected $skip = false;
    protected $lastProcessedAt;
    protected $lastProcessedStatus;
    protected $links;

    public function __construct()
    {
        $this->links = new ArrayCollection();
    }

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

    public function getParsedConfig()
    {
        return $this->parseConfig();
    }

    private function parseConfig()
    {
        if ($this->parsedConfig) {
            return $this->parsedConfig;
        }
        try {

            $parser = new \Symfony\Component\Yaml\Parser();
            // Use a Symfony ConfigurationInterface object to specify the *.yml format
            $yamlConfiguration = new \Nz\CrawlerBundle\Client\Configuration();

            // Process the configuration files (merge one-or-more *.yml files)
            $processor = new \Symfony\Component\Config\Definition\Processor();
            $this->parsedConfig = $processor->processConfiguration(
                $yamlConfiguration, array($parser->parse($this->config)) // As many *.yml files as required
            );
        } catch (InvalidConfigurationException $ex) {
            return false;
        } catch (ParseException $ex) {
            return false;
        }

        return $this->parsedConfig;
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
    public function setLastProcessedStatus($lastProcessedStatus = null)
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

    public function addLink(Link $link)
    {
        $link->setProfile($this);
        $this->links->add($link);
    }

    public function setLinks(array $links)
    {
        $this->links = new ArrayCollection();

        foreach ($links as $link) {
            $this->addLink($link);
        }

        return $this;
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function getTodoLinks()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('processed', false));
        $criteria->andWhere(Criteria::expr()->eq('error', false));
        $criteria->andWhere(Criteria::expr()->eq('skip', false));

        return $this->links->matching($criteria);
    }
    public function getProcessedLinks()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('processed', true));

        return $this->links->matching($criteria);
    }

    public function getErrorLinks()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('error', true));

        return $this->links->matching($criteria);
    }

    public function __toString()
    {
        return $this->name;
    }
}
