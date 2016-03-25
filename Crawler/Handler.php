<?php

namespace Nz\CrawlerBundle\Crawler;

use Nz\CrawlerBundle\Entity\Link;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Nz\CrawlerBundle\Client\ClientInterface;
use Nz\CrawlerBundle\Client\ClientException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Buzz\Exception\RequestException;
use Nz\CrawlerBundle\Entity\Profile;

/**
 * Crawl handler
 */
class Handler extends BaseHandler implements HandlerInterface
{

    public function urlsToLinks(array $urls, $persist = false)
    {
        $this->errors = array();

        $links = array();
        foreach ($urls as $key => $value) {
            $url = is_int($key) ? $value : $key;
            $title = is_int($key) ? null : $value;

            $link = $this->getEntityManager()->getRepository(Link::class)->findOneBy(['url' => $url]);
            if ($link) {

                $this->errors[] = new ClientException(sprintf('Duplicate link url: %s', $url));
            } else {
                $link = new Link();
                $link->setName($title);
                $link->setUrl($url);

                if ($persist) {
                    $this->getEntityManager()->persist($link);
                }
            }

            $links[] = $link;
        }


        return $links;
    }

    /**
     * {@inheritdoc}
     */
    public function handleIndex(ClientInterface $client, $persist = false)
    {

        try {

            $links = $this->urlsToLinks($client->getIndexUrls(), $persist);

            if ($persist) {
                $this->getEntityManager()->flush();
            }
        } catch (\Exception $ex) {
            $links = array();
            $this->errors[] = $ex;
        }


        return $links;
    }

    /**
     * {@inheritdoc}
     */
    public function handleLinks(ClientInterface $client, array $links, $persist = false)
    {
        $this->errors = array();
        $entities = array();
        foreach ($links as $link) {
            $client->resetLink($link);

            $entity = $this->handleLink($client, $persist);

            if (!$entity) {
                continue;
            }
            $entities[] = $entity;
        }

        return $entities;
    }

    /**
     * {@inheritdoc}
     */
    public function handleLink(ClientInterface $client, $persist = false)
    {

        $link = $client->getLink();

        if (!$link) {
            return false;
        }

        try {

            $entity = $client->createEntity();

            $client->crawlToEntity($entity);
            $link->setItems($client->getItems());

            $link->setNote('crawled_entity', sprintf('crawled entity: %s', $entity->getTitle()));

            if ($persist) {
                $link->setProcessed(true);

                $this->persistEntity($entity);
                $client->afterEntityPersist($entity);
                $link->setNote('created_entity', sprintf('created entity: %s:%d', get_class($entity), $entity->getId()));
            }

            $link->setSkip(false);
            $link->setError(false);
            if ($link->getId() !== NULL || $persist) {
                $this->persistLink($link);
            }

            return $entity;
        } catch (UniqueConstraintViolationException $ex) {

            $link->setNote('duplicate_entity_ex', $ex->getMessage());
        } catch (NotNullConstraintViolationException $ex) {

            $link->setNote('not_null_exception', 'Entity with required field empty');
        } catch (ClientException $ex) {

            $link->setNote('entity_client_exeption', $ex->getMessage());
        } catch (RequestException $ex) {

            $link->setNote('error_requesting_remote', $ex->getMessage());
        } catch (\Exception $ex) {
            $link->setNote('exception', $ex->getMessage());
            $link->setSkip(true);
        }

        $this->errors[] = $ex;
        $link->setError(true);
        if ($persist) {
            $this->persistLink($link);
        }

        return false;
    }
}
