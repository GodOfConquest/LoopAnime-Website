<?php

namespace LoopAnime\AppBundle\Tests\Crawler\Service;

use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesSeasons;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CrawlerServiceTest extends KernelTestCase
{

    public function setUp()
    {
        parent::setUp();
        self::bootKernel();
    }

    /**
     * @test
     */
    public function can_crawl_episode()
    {
        $anime = $this->prophesize(Animes::class);
        $anime->getTitle()->willReturn('Naruto Shippuuden');
        $anime->getId()->willReturn(1);
        $anime = $anime->reveal();

        $season = $this->prophesize(AnimesSeasons::class);
        $season->getAnime()->willReturn($anime);
        $season->getSeason()->willReturn(1);
        $season = $season->reveal();

        $episode = $this->prophesize(AnimesEpisodes::class);
        $episode->getSeason()->willReturn($season);
        $episode->getEpisode()->willReturn(2);
        $episode->getAbsoluteNumber()->willReturn(2);
        $episode = $episode->reveal();
        $hoster = self::$kernel->getContainer()->get('crawler.hoster.anitube');

        $crawlerService = self::$kernel->getContainer()->get('crawler.service');
        $crawlerService->addStrategy(self::$kernel->getContainer()->get('crawler.strategy.episode'));
        $mirrors = $crawlerService->crawlEpisode($episode, $hoster);

        $this->assertEquals(["http://www.anitube.se/embed/5d8825d7027efd5b358a"], $mirrors);
    }


}
