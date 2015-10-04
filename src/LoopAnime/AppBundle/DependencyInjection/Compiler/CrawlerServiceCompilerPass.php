<?php

namespace LoopAnime\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CrawlerServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('crawler.service')) {
            return;
        }

        $definition = $container->getDefinition(
            'crawler.service'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'crawler.strategy'
        );
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addStrategy',
                array(new Reference($id))
            );
        }

        $taggedServices = $container->findTaggedServiceIds(
            'crawler.hoster'
        );
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addHoster',
                array(new Reference($id))
            );
        }
    }
}
