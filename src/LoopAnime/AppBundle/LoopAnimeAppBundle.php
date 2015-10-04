<?php

namespace LoopAnime\AppBundle;

use LoopAnime\AppBundle\DependencyInjection\Compiler\CrawlerServiceCompilerPass;
use LoopAnime\AppBundle\DependencyInjection\Compiler\QueueServiceCompilerPass;
use LoopAnime\AppBundle\DependencyInjection\Compiler\SyncHandlersCompilerPass;
use LoopAnime\AppBundle\DependencyInjection\Compiler\WorkerFactoryCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LoopAnimeAppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SyncHandlersCompilerPass());
        $container->addCompilerPass(new WorkerFactoryCompilerPass());
        $container->addCompilerPass(new QueueServiceCompilerPass());
        $container->addCompilerPass(new CrawlerServiceCompilerPass());
    }
}
