<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            new FOS\UserBundle\FOSUserBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),

            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle(),

            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\MarkdownBundle\KnpMarkdownBundle(),
            new Ivory\CKEditorBundle\IvoryCKEditorBundle(),

            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\FormatterBundle\SonataFormatterBundle(),
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
            new Sonata\UserBundle\SonataUserBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),

            new LoopAnime\AppBundle\LoopAnimeAppBundle(),
            new LoopAnime\AdminBundle\LoopAnimeAdminBundle(),
            new LoopAnime\SearchBundle\LoopAnimeSearchBundle(),
            new LoopAnime\ApiBundle\LoopAnimeApiBundle(),
            new LoopAnime\ShowsBundle\LoopAnimeShowsBundle(),
            new LoopAnime\ShowsAPIBundle\LoopAnimeShowsAPIBundle(),
            new LoopAnime\UsersBundle\LoopAnimeUsersBundle(),
            new LoopAnime\CommentsBundle\LoopAnimeCommentsBundle(),
            new LoopAnime\CrawlersBundle\LoopAnimeCrawlersBundle(),
            new LoopAnime\WelcomeBundle\LoopAnimeWelcomeBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getCacheDir()
    {
        return '/tmp/symfony/cache/'. $this->environment;
    }

    public function getLogDir()
    {
        return '/tmp/symfony/log/'. $this->environment;
    }
}
