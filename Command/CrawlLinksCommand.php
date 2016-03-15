<?php

namespace Nz\CrawlerBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class CrawlLinksCommand extends BaseCrawlCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('nz:crawl:links');
        $this->addOption('persist', null, InputOption::VALUE_NONE, 'persist');
        $this->setDescription('Crawl Links Command');
    }

    /**
     * Crawl links
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {

        $linkManager = $this->getLinkManager();
        $handler = $this->getHandler();
        $clientPool = $this->getClientPool();

        $persist = ($input->getOption('persist')) ? true : false;
        $links = $linkManager->findLinksForProcess(5);
        $errors = [];
        $entities = [];
        ini_set('max_execution_time', 0);
        foreach ($links as $link) {
            $client = $clientPool->getEntityClientForLink($link);

            if ($client) {
                $entity = $handler->handleEntityClient($client, $persist);

                if (!$entity) {
                    $notes = $link->getNotes();
                    $errors[] = end($notes);
                } else {
                    $entities[] = $entity->getTitle();
                }
            } else {
                $output->writeln(sprintf('No Entity Client for link url: %s', $link->getUrl()));
            }
        }

        $msg = sprintf('Links: %s, Success: %s, Errors: %s, persist: %d', count($links), count($entities), count($errors), $persist);

        $output->writeln($msg);
        return $msg;
    }
}
