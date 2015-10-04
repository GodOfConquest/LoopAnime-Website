<?php

namespace LoopAnime\AppBundle\Crawler\Hoster;

use LoopAnime\AppBundle\Crawler\Enum\AnimeHosterEnum;
use LoopAnime\AppBundle\Crawler\Enum\HosterLanguageEnum;
use LoopAnime\AppBundle\Crawler\Enum\StrategyEnum;
use LoopAnime\AppBundle\Crawler\Enum\VideoQualityEnum;

class Anime44Hoster extends AbstractHoster implements HosterInterface
{

    protected $searchLink = "http://www.anime44.com/anime/search?key={search_term}&search_submit=Go";

    public function getNextPage($link, $page)
    {
        if (strpos($link, "/page/") === false && strpos($link, "/search") === false) {
            $link = $link . '/page/' . $page;
        }
        return preg_replace('/page\/\d+/', 'page/' . $page, $link);
    }


    public function getEpisodeMirrors($link)
    {
        $webpage_content = file_get_contents($link);
        preg_match_all('/iframe.+?(http.+?(?:embed|mp4|w\=|h\=|width|height).+?)"/mi', $webpage_content, $match);
        $mirrors = [];
        foreach ($match[1] as $mirror) {
            $mirrors[] = $mirror;
        }

        return array_unique($mirrors);
    }

    public function getDirectLinks($link)
    {
        $webContent = file_get_contents($link);
        preg_match_all('/url.+(http.+(?:mp4|videoplayback|flv).*?)[\"\']/mi', $webContent, $matchs);
        $mirrors = [];
        foreach ($matchs[1] as $match) {
            $mirrors[VideoQualityEnum::DEFAULT_QUALITY][] = urldecode($match);
        }

        return array_unique($mirrors);
    }

    public function getSubtitles()
    {
        return HosterLanguageEnum::ENGLISH;
    }

    public function getStrategy()
    {
        return StrategyEnum::STRATEGY_SERIE_SEARCH;
    }

    public function getName()
    {
        return AnimeHosterEnum::HOSTER_ANIME44;
    }

}
