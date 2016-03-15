<?php

namespace Nz\CrawlerBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class CrawlIndexesCommand extends BaseCrawlCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('nz:crawl:indexes');
        $this->addOption('persist', null, InputOption::VALUE_NONE, 'Persist');

        $this->setDescription('Crawl Indexes Command');
    }

    /**
     * Crawl Indexes
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $handler = $this->getHandler();
        $clientPool = $this->getClientPool();
        $clients_indexes = $clientPool->getIndexClients();
        $persist = ($input->getOption('persist')) ? true : false;

        $links = [];
        $errors = [];
        foreach ($clients_indexes as $client) {
            $l = $handler->handleIndexClient($client, $persist);

            $links = array_merge($links, $l);

            $e = $handler->getErrors();
            $errors = array_merge($errors, $e);
        }

        $output->writeln(sprintf('New Links: %s ', count($links)));
        foreach ($links as $link) {
            $output->writeln(sprintf('Url: %s ', $link->getUrl()));
        }

        $output->writeln(sprintf('Errors: %s ', count($errors)));
        foreach ($errors as $err) {
            $notes = $err->getNotes();
            $output->writeln(sprintf('Note: %s ', end($notes)));
        }

        $output->writeln(sprintf('Clients: %s, Links: %s, Errors: %s, persist: %d', count($clients_indexes), count($links), count($errors), $persist));
    }
}
