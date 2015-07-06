<?php

namespace LoopAnime\AppBundle\Crawler\Hoster;

use LoopAnime\AppBundle\Crawler\Enum\AnimeHosterEnum;
use LoopAnime\AppBundle\Crawler\Enum\StrategyEnum;
use LoopAnime\AppBundle\Crawler\Enum\VideoQualityEnum;

class Anime44Hoster extends AbstractHoster
{

    protected $searchLink = "http://www.anime44.com/anime/search?key={search_term}&search_submit=Go";

    public function getNextPage($link, $page)
    {
        if(strpos($link,"/page/") === false) {
            $link = $link . '/page/' . $this->page;
        }
        return preg_replace('/page\/\d+/','page/'.$this->page,$link);
    }


    public function getEpisodeMirros($link)
    {
        $linkOriginal = $link;
        $webpage_content = file_get_contents($link);
        $url = parse_url($link);
        $host = str_replace(array("www.",".com",".pt",".info",".es",".me",".net",".com.br","embed.","org"),"",$url["host"]);
        switch($host) {
            case "play44":
            case "video44":
            case "byzoo":
                $matchs = [];
                preg_match_all("/_url.*=.*\"(.+)\"/m",$webpage_content,$matchs);
                $link = $matchs[1][0];
                break;

            case "videofun":
                $offset = 0;
                $webpage_content = $this->extractContent($webpage_content, "playlist: ", $offset, "[", "[", "]");
                $i = 1;
                $offset = 0;
                while(substr_count($webpage_content, "url:") >= $i) {
                    $i++;
                    $link = "http://" . trim($this->extractContent($webpage_content, "{", $offset, "url:", 'http://', ','),"',".'"');
                    if(strpos($link, ".jpg") === false && strlen($link) > strlen($host) + 10)
                        break;
                }
                break;

            case "playbb":
            case "easyvideo":
            case "videozoo":
            case "vidzur":
            case "videowing":
                $re = '/_url = "(http.*?)"/mi';
                $matches = [];
                preg_match_all($re, $webpage_content, $matches);
                $link = $matches[1][0];
                break;

            case "yourupload":
                $offset = 0;
                $link = "http:" . $this->extractContent($webpage_content, 'jwplayer', $offset, "'file':", "'http:", "'");
                break;
        }

        if($linkOriginal === $link)
            return false;
        return [VideoQualityEnum::DEFAULT_QUALITY => [$link]];
    }

    // Extract content
    private function extractContent($webpage_content, $offset_content, &$offset, $look4var, $from_string, $to_string) {
        $offset 	= strpos($webpage_content, $offset_content, $offset);
        $var 		= strpos($webpage_content, $look4var, $offset);
        $pos_init 	= strpos($webpage_content, $from_string, $var) + strlen($from_string);
        $pos_end 	= strpos($webpage_content, $to_string, $pos_init);
        $offset		= $pos_end;

        $substr = substr($webpage_content, $pos_init, $pos_end - $pos_init);
        return $substr;
    }

    public function getSubtitles()
    {
        return "EN";
    }

    public function getStrategy()
    {
        return StrategyEnum::STRATEGY_ANIME_SEARCH;
    }

    public function getName()
    {
        return AnimeHosterEnum::HOSTER_ANIME44;
    }

}
