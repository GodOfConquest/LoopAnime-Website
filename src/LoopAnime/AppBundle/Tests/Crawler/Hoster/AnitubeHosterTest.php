<?php

namespace LoopAnime\AppBundle\Tests\Crawler\Hoster;

use LoopAnime\AppBundle\Crawler\Enum\HosterLanguageEnum;
use LoopAnime\AppBundle\Crawler\Enum\StrategyEnum;
use LoopAnime\AppBundle\Crawler\Enum\VideoQualityEnum;
use LoopAnime\AppBundle\Crawler\Hoster\AnitubeHoster;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AnitubeHosterTest extends KernelTestCase
{
    /** @var  AnitubeHoster */
    protected $hoster;

    public function setUp()
    {
        parent::setUp();
        self::bootKernel();
        $this->hoster = self::$kernel->getContainer()->get('crawler.hoster.anitube');
    }

    /**
     * @test
     */
    public function can_get_next_page()
    {
        $uri = "http://www.anitube.se/search/?search_id=naruto+shippuden";
        $nextPage = $this->hoster->getNextPage($uri, 2);

        $this->assertEquals("http://www.anitube.se/search/2/?search_id=naruto+shippuden", $nextPage);
    }

    /**
     * @test
     */
    public function subtitles_are_english()
    {
        $this->assertEquals(HosterLanguageEnum::BRAZILIAN, $this->hoster->getSubtitles());
    }

    /**
     * @test
     */
    public function assert_that_is_serie_strategy()
    {
        $this->assertEquals(StrategyEnum::STRATEGY_EPISODE_SEARCH, $this->hoster->getStrategy());
    }

    /**
     * @test
     */
    public function can_grab_mirrors_from_link()
    {
        $link = "http://www.anitube.se/video/58524/Naruto-Cl%C3%A1ssico-064";
        $mirrors = $this->hoster->getEpisodeMirrors($link);
        $this->assertEquals([
            "http://www.anitube.se/embed/d535703a9e5e722ce75e",
        ], $mirrors);

        return $mirrors;
    }

    /**
     * @test
     * @depends can_grab_mirrors_from_link
     * @param $mirrors
     */
    public function can_grab_direct_links($mirrors)
    {
        $mirrors = $this->hoster->getDirectLinks($mirrors[0]);
        $this->assertCount(2, $mirrors[VideoQualityEnum::DEFAULT_QUALITY]);
    }

}
