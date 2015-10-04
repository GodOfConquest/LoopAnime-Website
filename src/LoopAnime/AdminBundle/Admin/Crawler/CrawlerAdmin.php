<?php

namespace LoopAnime\AdminBundle\Admin\Crawler;

use LoopAnime\AppBundle\Crawler\Enum\AnimeHosterEnum;
use LoopAnime\AppBundle\Crawler\Enum\SerieHosterEnum;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class CrawlerAdmin extends Admin
{
    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('id')
            ->add('anime')
            ->add('hoster')
            ->add('settings')
            ->add('episodeClean')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('id')
            ->add('anime')
            ->add('hoster')
        ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('anime')
            ->add('hoster', 'choice', ['choices' => array_merge(AnimeHosterEnum::getAsChoices(), SerieHosterEnum::getAsChoices())])
            ->add('settings', 'sonata_type_collection', array(
                'by_reference'       => false,
                'cascade_validation' => true,
            ), array(
                'edit' => 'inline',
                'inline' => 'table'
            ))
            ->add('episodeClean')
        ;
    }
}
