<?php

namespace Nz\CrawlerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class UrlsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* dd($builder); */
        $builder
            ->add('urls', TextareaType::class, array('label' => false, 'required' => false))
            ->add('save', SubmitType::class, array('label' => 'Crawl Urls'))
        ;
    }
}
