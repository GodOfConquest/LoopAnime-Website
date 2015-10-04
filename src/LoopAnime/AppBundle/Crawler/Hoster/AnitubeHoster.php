<?php

namespace LoopAnime\AppBundle\Crawler\Hoster;

use LoopAnime\AppBundle\Crawler\Enum\AnimeHosterEnum;
use LoopAnime\AppBundle\Crawler\Enum\HosterLanguageEnum;
use LoopAnime\AppBundle\Crawler\Enum\StrategyEnum;
use LoopAnime\AppBundle\Crawler\Enum\VideoQualityEnum;
use LoopAnime\AppBundle\Crawler\Strategy\EpisodeSearchStrategy;

class AnitubeHoster extends AbstractHoster implements HosterInterface
{

    protected $searchLink = "http://www.anitube.se/search/?search_id={search_term}";

    public function getNextPage($link, $page)
    {
        if (preg_match('/^.+search\/\?.+$/', $link)) {
            $link = str_replace("search/?", "search/" . $page . "/?", $link);
        }
        return preg_replace('/search\/(\d)\/\?/', 'search/' . $page . '/?', $link);
    }

    /**
     * Parse the XML of playlist and searchs for the video file
     * @param string $link Link to the XML file / playlist
     * @return string|boolean
     */
    public function getEpisodeMirrors($link)
    {
        $webpage_content = file_get_contents($link);
        preg_match_all('/iframe.+?(http.+?(?:\/embed\/|mp4|w\=|h\=|width|height).+?)"/mi', $webpage_content, $match);
        $mirrors = [];
        foreach ($match[1] as $mirror) {
            $mirrors[] = $mirror;
        }

        return $mirrors;
    }

    public function getDirectLinks($link)
    {
        $configLink = "http://www.anitube.se/nuevo/econfig.php?key=" . basename($link);
        $mirrors = [];

        if ($playerXML = simplexml_load_file($configLink)) {
            $playlistLink = (string)$playerXML->playlist;
            // Check if this file is a playlist
            if (!empty($playlistLink)) {
                $playerXML = simplexml_load_file($playlistLink)->trackList->track;
            }

            if (!empty((string)$playerXML->file)) {
                $mirrors[VideoQualityEnum::DEFAULT_QUALITY][] = (string)$playerXML->file;
            }
            if (!empty((string)$playerXML->html5)) {
                $mirrors[VideoQualityEnum::DEFAULT_QUALITY][] = (string)$playerXML->html5;
            }
            if (!empty((string)$playerXML->filehd)) {
                $mirrors[VideoQualityEnum::HIGHT_QUALITY][] = (string)$playerXML->filehd;
            }
        }

        return $mirrors;
    }

    public function getSubtitles()
    {
        return HosterLanguageEnum::BRAZILIAN;
    }

    public function getStrategy()
    {
        return StrategyEnum::STRATEGY_EPISODE_SEARCH;
    }

    public function getName()
    {
        return AnimeHosterEnum::HOSTER_ANITUBE;
    }
}
