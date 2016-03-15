<?php

namespace Nz\CrawlerBundle\Crawler;

use Nz\CrawlerBundle\Entity\Link;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nz\CrawlerBundle\Client\IndexClientInterface;
use Nz\CrawlerBundle\Client\EntityClientInterface;
use Nz\CrawlerBundle\Client\ClientException;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Base crawl Handler
 */
abstract class BaseHandler implements HandlerInterface
{

    protected $managerRegistry;
    protected $entityClass;
    protected $errors = [];
    protected $links = [];

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    protected function getNewEntity()
    {
        return new $this->entityClass();
    }

    /**
     *  Get entity manager
     * 
     *  @return \Doctrine\ORM\EntityManager Entity Manager
     */
    protected function getEntityManager()
    {
        if (!$this->managerRegistry->getManager()->isOpen()) {
            $this->managerRegistry->resetManager();
        }

        return $this->managerRegistry->getManager();
    }

    /**
     *  Persist/sabe link to db
     * 
     * @param \Nz\CrawlerBundle\Model\LinkInterface $link Link entity
     */
    protected function persistLink(Link $link)
    {

        $em = $this->getEntityManager();

        if (null !== $link->getId()) {
            $em->merge($link);
        } else {

            $em->persist($link);
        }

        try {

            $em->flush();

            return true;
        } catch (UniqueConstraintViolationException $ex) {

            $this->errors[] = array_pop($this->links);
            $link->setNote('duplicate_link_url', sprintf('Duplicate link url: %s', $link->getUrl()));

            return false;
        }
    }

    /**
     * Persist Entity
     * 
     * @param object $entity The entity to persist
     */
    protected function persistEntity($entity)
    {
        $em = $this->getEntityManager();

        $em->persist($entity);

        $em->flush();
    }

    /**
     * Return errors
     * 
     * @return array Errors
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
