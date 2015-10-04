<?php

namespace LoopAnime\AppBundle\Tests\Crawler\Hoster;

use LoopAnime\AppBundle\Crawler\Enum\HosterLanguageEnum;
use LoopAnime\AppBundle\Crawler\Enum\StrategyEnum;
use LoopAnime\AppBundle\Crawler\Enum\VideoQualityEnum;
use LoopAnime\AppBundle\Crawler\Hoster\Anime44Hoster;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class Anime44HosterTest extends KernelTestCase
{
    /** @var  Anime44Hoster */
    protected $hoster;

    public function setUp()
    {
        parent::setUp();
        self::bootKernel();
        $this->hoster = self::$kernel->getContainer()->get('crawler.hoster.anime44');
    }

    /**
     * @test
     */
    public function can_get_next_page()
    {
        $uri = "http://www.anime44.com/categories/naruto-shippuden";
        $nextPage = $this->hoster->getNextPage($uri, 2);

        $this->assertEquals("http://www.anime44.com/categories/naruto-shippuden/page/2", $nextPage);
    }

    /**
     * @test
     */
    public function subtitles_are_english()
    {
        $this->assertEquals(HosterLanguageEnum::ENGLISH, $this->hoster->getSubtitles());
    }

    /**
     * @test
     */
    public function assert_that_is_serie_strategy()
    {
        $this->assertEquals(StrategyEnum::STRATEGY_SERIE_SEARCH, $this->hoster->getStrategy());
    }

    /**
     * @test
     */
    public function can_grab_mirrors_from_link()
    {
        $link = "http://www.animenova.org/gintama-2015-episode-14";
        $mirrors = $this->hoster->getEpisodeMirrors($link);
        $this->assertEquals([
            "http://videowing.me/embed?w=718&h=438&video=ongoing/gintama_2015_-_14.mp4",
            "http://playbb.me/embed.php?w=718&h=438&vid=at/nw/gintama_2015_-_14.mp4",
            "http://videozoo.me/embed.php?w=718&h=438&vid=at/nw/gintama_2015_-_14.mp4",
            "http://www.easyvideo.me/gogo/?w=718&h=438&file=gintama_2015_-_14.mp4&sv=1",
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
        $this->assertCount(1, $mirrors[VideoQualityEnum::DEFAULT_QUALITY]);
    }

    /**
     * @test
     */
    public function can_grab_direct_link_from_videowing()
    {
        $link = "http://videowing.me/embed?w=718&h=438&video=ongoing/gintama_2015_-_14.mp4";
        $mirrors = $this->hoster->getDirectLinks($link);
        $this->assertRegExp('/gateway.play44.net/', $mirrors[VideoQualityEnum::DEFAULT_QUALITY][0]);
    }

    /**
     * @test
     */
    public function can_grab_direct_link_from_playbbme()
    {
        $link = "http://playbb.me/embed.php?w=718&h=438&vid=at/nw/gintama_2015_-_14.mp4";
        $mirrors = $this->hoster->getDirectLinks($link);
        $this->assertRegExp('/gateway.play44.net/', $mirrors[VideoQualityEnum::DEFAULT_QUALITY][0]);
    }

    /**
     * @test
     */
    public function can_grab_direct_link_from_videozoome()
    {
        $link = "http://videozoo.me/embed.php?w=718&h=438&vid=at/nw/gintama_2015_-_14.mp4";
        $mirrors = $this->hoster->getDirectLinks($link);
        $this->assertRegExp('/redirector.googlevideo.com/', $mirrors[VideoQualityEnum::DEFAULT_QUALITY][0]);
    }

    /**
     * @test
     */
    public function can_grab_direct_link_from_easyvideo()
    {
        $link = "http://www.easyvideo.me/gogo/?w=718&h=438&file=gintama_2015_-_14.mp4&sv=1";
        $mirrors = $this->hoster->getDirectLinks($link);
        $this->assertRegExp('/redirector.googlevideo.com/', $mirrors[VideoQualityEnum::DEFAULT_QUALITY][0]);
    }

    /**
     * @test
     */
    public function can_grab_new_links_for_videowing()
    {
        $link = "http://videowing.me/embed/c5fd24f6e2378ad0927f46874b2a65ca?w=718&h=438";
        $mirrors = $this->hoster->getDirectLinks($link);
        $this->assertRegExp('/gateway.play44.net/', $mirrors[VideoQualityEnum::DEFAULT_QUALITY][0]);
    }

}
