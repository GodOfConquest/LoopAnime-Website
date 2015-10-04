<?php

namespace LoopAnime\AppBundle\Tests\Crawler\Strategy;

use LoopAnime\AppBundle\Crawler\Hoster\Anime44Hoster;
use LoopAnime\AppBundle\Crawler\Strategy\SerieSearchStrategy;
use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesSeasons;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SerieSearchStrategyTest extends KernelTestCase
{

    public function setUp()
    {
        parent::setUp();
        self::bootKernel();
    }

    /**
     * @test
     */
    public function can_execute_strategy()
    {
        $anime = $this->prophesize(Animes::class);
        $anime->getTitle()->willReturn('One Piece');
        $anime->getId()->willReturn(1);
        $anime = $anime->reveal();

        $season = $this->prophesize(AnimesSeasons::class);
        $season->getAnime()->willReturn($anime);
        $season->getSeason()->willReturn(1);
        $season = $season->reveal();

        $episode = $this->prophesize(AnimesEpisodes::class);
        $episode->getSeason()->willReturn($season);
        $episode->getEpisode()->willReturn(479);
        $episode->getAbsoluteNumber()->willReturn(479);
        /** @var AnimesEpisodes $episode */
        $episode = $episode->reveal();

        $crawlerService = self::$kernel->getContainer()->get('crawler.service');
        $crawlerService->addStrategy(self::$kernel->getContainer()->get('crawler.strategy.episode'));

        /** @var Anime44Hoster $hoster */
        $hoster = self::$kernel->getContainer()->get('crawler.hoster.anime44');
        /** @var SerieSearchStrategy $serieStrategy */
        $serieStrategy = self::$kernel->getContainer()->get('crawler.strategy.serie');

        $guesser = $serieStrategy->execute($episode, $hoster);
        $this->assertEquals(true, $guesser->isExactMatch());
        var_dump($guesser->getUri());
        var_dump($guesser->getLog());
    }

    /**
     * @test
     */
    public function can_execute_under_animegg()
    {
        $anime = $this->prophesize(Animes::class);
        $anime->getTitle()->willReturn('naruto');
        $anime->getId()->willReturn(1);
        $anime = $anime->reveal();

        $season = $this->prophesize(AnimesSeasons::class);
        $season->getAnime()->willReturn($anime);
        $season->getSeason()->willReturn(1);
        $season = $season->reveal();

        $episode = $this->prophesize(AnimesEpisodes::class);
        $episode->getSeason()->willReturn($season);
        $episode->getEpisode()->willReturn(5);
        $episode->getAbsoluteNumber()->willReturn(5);
        /** @var AnimesEpisodes $episode */
        $episode = $episode->reveal();

        $crawlerService = self::$kernel->getContainer()->get('crawler.service');
        $crawlerService->addStrategy(self::$kernel->getContainer()->get('crawler.strategy.episode'));

        /** @var Anime44Hoster $hoster */
        $hoster = self::$kernel->getContainer()->get('crawler.hoster.animegg');
        /** @var SerieSearchStrategy $serieStrategy */
        $serieStrategy = self::$kernel->getContainer()->get('crawler.strategy.serie');

        $guesser = $serieStrategy->execute($episode, $hoster);
        $this->assertEquals(true, $guesser->isExactMatch());
        var_dump($guesser->getUri());
        var_dump($guesser->getLog());
    }

}
