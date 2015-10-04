<?php
namespace LoopAnime\AppBundle\Crawler\Hoster;


use LoopAnime\AppBundle\Crawler\Enum\AnimeHosterEnum;
use LoopAnime\AppBundle\Crawler\Enum\HosterLanguageEnum;
use LoopAnime\AppBundle\Crawler\Enum\StrategyEnum;
use LoopAnime\AppBundle\Crawler\Enum\VideoQualityEnum;

class AnimeggHoster extends AbstractHoster implements HosterInterface
{

    protected $searchLink = "http://www.animegg.org/search?search={search_term}";

    public function getEpisodeMirrors($link)
    {
        $webpage_content = file_get_contents($link);
        preg_match_all('/iframe.*?src="(.*?embed.*?)"/mi', $webpage_content, $match);
        $mirrors = [];
        foreach ($match[1] as $mirror) {
            if (strpos($mirror, "http://www.animegg.org/") === false) {
                $mirror = "http://www.animegg.org" . $mirror;
            }
            $mirrors[] = $mirror;
        }
        return $mirrors;
    }

    public function getDirectLinks($link)
    {
        $webpage_content = file_get_contents($link);
        preg_match_all('/file: "(.*?)".*?label: "(.*?)"/mi', $webpage_content, $match);
        $mirrors = [];
        foreach ($match[1] as $key => $mirror) {
            $quality = VideoQualityEnum::DEFAULT_QUALITY;
            if ((int)$match[2][$key] > 480) {
                $quality = VideoQualityEnum::HIGHT_QUALITY;
            }
            $mirrors[$quality][] = $mirror;
        }

        return $mirrors;
    }

    public function getSubtitles()
    {
        return HosterLanguageEnum::ENGLISH;
    }

    public function getStrategy()
    {
        return StrategyEnum::STRATEGY_SERIE_SEARCH;
    }

    public function isPaginated()
    {
        return false;
    }

    public function isIframe()
    {
        return true;
    }

    public function getName()
    {
        return AnimeHosterEnum::HOSTER_ANIMEGG;
    }
}
