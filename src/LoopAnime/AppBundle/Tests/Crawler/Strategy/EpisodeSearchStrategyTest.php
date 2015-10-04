<?php
namespace LoopAnime\AppBundle\Tests\Crawler\Strategy;


use LoopAnime\AppBundle\Crawler\Enum\StrategyEnum;
use LoopAnime\AppBundle\Crawler\Hoster\Anime44Hoster;
use LoopAnime\AppBundle\Crawler\Hoster\AnitubeHoster;
use LoopAnime\AppBundle\Crawler\Strategy\EpisodeSearchStrategy;
use LoopAnime\ShowsBundle\Entity\Animes;
use LoopAnime\ShowsBundle\Entity\AnimesEpisodes;
use LoopAnime\ShowsBundle\Entity\AnimesSeasons;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EpisodeSearchStrategyTest extends KernelTestCase
{

    protected $crawlerRepo;

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
        $anime->getTitle()->willReturn('Naruto Shippuuden');
        $anime->getId()->willReturn(1);
        $anime = $anime->reveal();

        $season = $this->prophesize(AnimesSeasons::class);
        $season->getAnime()->willReturn($anime);
        $season->getSeason()->willReturn(1);
        $season = $season->reveal();

        $episode = $this->prophesize(AnimesEpisodes::class);
        $episode->getSeason()->willReturn($season);
        $episode->getEpisode()->willReturn(11);
        $episode->getAbsoluteNumber()->willReturn(11);
        $episode = $episode->reveal();
        $hoster = self::$kernel->getContainer()->get('crawler.hoster.anitube');

        $crawlerService = self::$kernel->getContainer()->get('crawler.service');
        $crawlerService->addStrategy(self::$kernel->getContainer()->get('crawler.strategy.episode'));

        /** @var EpisodeSearchStrategy $episodeStrategy */
        $episodeStrategy = $crawlerService->getStrategy(StrategyEnum::STRATEGY_EPISODE_SEARCH);
        $guesser = $episodeStrategy->execute($episode, $hoster);
        $this->assertEquals(true, $guesser->isExactMatch());
    }

    /**
     * @test
     */
    public function can_find_merged_episodes()
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
        $episode->getEpisode()->willReturn(1);
        $episode->getAbsoluteNumber()->willReturn(1);
        $episode = $episode->reveal();
        $hoster = self::$kernel->getContainer()->get('crawler.hoster.anitube');

        $crawlerService = self::$kernel->getContainer()->get('crawler.service');
        $crawlerService->addStrategy(self::$kernel->getContainer()->get('crawler.strategy.episode'));

        /** @var EpisodeSearchStrategy $episodeStrategy */
        $episodeStrategy = $crawlerService->getStrategy(StrategyEnum::STRATEGY_EPISODE_SEARCH);
        $guesser = $episodeStrategy->execute($episode, $hoster);
        $this->assertEquals(true, $guesser->isExactMatch());
    }

    /**
     * @test
     */
    public function can_find_merged_episodes_given_next_episode()
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

        /** @var EpisodeSearchStrategy $episodeStrategy */
        $episodeStrategy = $crawlerService->getStrategy(StrategyEnum::STRATEGY_EPISODE_SEARCH);
        $guesser = $episodeStrategy->execute($episode, $hoster);
        $this->assertEquals(true, $guesser->isExactMatch());
    }

}
