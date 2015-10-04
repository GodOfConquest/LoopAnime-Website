<?php
namespace LoopAnime\AppBundle\Tests\Crawler\Hoster;


use LoopAnime\AppBundle\Crawler\Enum\VideoQualityEnum;
use LoopAnime\AppBundle\Crawler\Hoster\AnimeggHoster;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AnimeggHosterTest extends KernelTestCase
{

    /** @var  AnimeggHoster */
    protected $hoster;

    public function setUp()
    {
        parent::setUp();
        self::bootKernel();
        $this->hoster = self::$kernel->getContainer()->get('crawler.hoster.animegg');
    }

    /**
     * @test
     */
    public function can_get_episode_mirrors()
    {
        $link = "http://www.animegg.org/naruto-shippuden-episode-419";
        $this->assertEquals(["http://www.animegg.org/embed/77797"], $this->hoster->getEpisodeMirrors($link));
    }

    /**
     * @test
     */
    public function getDirectLinks()
    {
        $link = "http://www.animegg.org/embed/77797";
        $mirrors = $this->hoster->getDirectLinks($link);

        $this->assertCount(2, $mirrors);
        $this->assertRegExp("/cdn.oose.io/", $mirrors[VideoQualityEnum::DEFAULT_QUALITY][0]);
        $this->assertRegExp("/cdn.oose.io/", $mirrors[VideoQualityEnum::HIGHT_QUALITY][0]);
    }



}
