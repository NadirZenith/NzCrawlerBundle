<?php

namespace Nz\CrawlerBundle\Block;

use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Event\BlockEvent;

class PreviewProfileEvent
{

    public function onProfileAdminTop(BlockEvent $event)
    {
        $admin = $event->getSetting('admin');
        /*
          $block = new Block();
          $block->setId(uniqid()); // set a fake id
          $block->setType('sonata.block.service.text');
          $block->setSetting('content', 'sonata text block <br>');
          $event->addBlock($block);

          d($event);
        dd($admin->getRequest());
         */

        //profile admin
        if ($admin->getCode() === 'nz.crawler.admin.profile') {
            //Preview Profile block
            $block = new Block();
            $block->setType('nz.crawler.block.preview_profile');
            $block->setId(uniqid()); // set a fake id
            $block->setSettings($event->getSettings());

            if ('admin_nz_crawler_profile_crawl-urls' === $admin->getRequest()->attributes->get('_route')) {
                $block->setSetting('urls_form', false);
            }

            $event->addBlock($block);
        }

        //link admin
        if ($admin->getCode() === 'nz.crawler.admin.link') {

            if ($admin->getParent()) {
                //Preview Profile block
                $block = new Block();
                $block->setType('nz.crawler.block.preview_profile');
                $block->setId(uniqid()); // set a fake id
                $block->setSettings($event->getSettings());
                $block->setSetting('admin', $admin->getParent());
                $block->setSetting('object', $admin->getParent()->getSubject());

                $event->addBlock($block);
            }
        }
    }

    public function onProfileAdminBottom(BlockEvent $event)
    {
        $admin = $event->getSetting('admin');

        if ($admin->getCode() === 'nz.crawler.admin.profile') {
            //Preview Profile block
            $block = new Block();
            $block->setId(uniqid()); // set a fake id
            $block->setSettings($event->getSettings());
            $block->setSetting('view_type', 'detail');
            $block->setType('nz.crawler.block.preview_profile');

            $event->addBlock($block);
        }
    }
}
