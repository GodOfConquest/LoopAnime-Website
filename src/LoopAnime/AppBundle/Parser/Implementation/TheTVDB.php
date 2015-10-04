<?php

namespace LoopAnime\AppBundle\Parser\Implementation;

use LoopAnime\AppBundle\Parser\Enum\ParserEnum;
use LoopAnime\AppBundle\Parser\Exception\PageCantBeParsedException;
use LoopAnime\AppBundle\Parser\Exception\PageNotFoundException;
use LoopAnime\AppBundle\Parser\Exception\ResponseEmptyException;
use LoopAnime\AppBundle\Parser\ParserAnime;
use LoopAnime\AppBundle\Parser\ParserEpisode;
use LoopAnime\AppBundle\Parser\ParserSeason;

class TheTVDB {

    const LINK_FULL_SHOW_INFO = 'http://thetvdb.com/api/{api_key}/series/{anime_key}/all/en.xml';
    const LINK_IMAGES = 'http://thetvdb.com/banners/{filename}';


    public function __construct($apiKey, $rootDir)
    {
        $this->apiKey = $apiKey;
        $this->rootDir = $rootDir;
    }

    /**
     * @param $animeId
     * @return ParserAnime
     * @throws PageCantBeParsedException
     * @throws PageNotFoundException
     * @throws ResponseEmptyException
     */
    public function parseAnime($animeId)
    {

        // Try get the information about the anime
        if (!$page_content = file_get_contents($this->makeLink(self::LINK_FULL_SHOW_INFO, $animeId)))
            throw new PageNotFoundException();

        if ($page_content == "")
            throw new ResponseEmptyException();

        // Parse the information returned
        if (!($response_simple = simplexml_load_string($page_content, 'SimpleXMLElement', LIBXML_NOCDATA)))
            throw new PageCantBeParsedException();

        $responseSimpleXml = $response_simple->Series;

        $parserAnime = new ParserAnime(
            (string)$responseSimpleXml->SeriesName,
            $this->downloadImage((string)$responseSimpleXml->poster),
            "",
            (string)$responseSimpleXml->Overview,
            (string)$responseSimpleXml->Runtime,
            (string)$responseSimpleXml->FirstAired,
            "",
            (string)$responseSimpleXml->Status,
            (string)$responseSimpleXml->Rating,
            (string)$responseSimpleXml->IMDB_ID,
            (string)$responseSimpleXml->RatingCount,
            trim(str_replace("|", ",", (string)$responseSimpleXml->Genre), " ,"),
            $animeId,
            ParserEnum::PARSER_TVDB
        );

        $seasons = [];

        foreach ($response_simple->Episode as $episode) {

            if (!empty($seasons[(string)$episode->SeasonNumber])) {
                $parserSeason = $seasons[(string)$episode->SeasonNumber];
            } else {
                $seasons[(string)$episode->SeasonNumber] = $parserSeason = new ParserSeason((string)$episode->SeasonNumber, '');
            }

            $parserEpisode = new ParserEpisode();
            $parserEpisode->setEpisodeNumber((string)$episode->EpisodeNumber);
            if (!empty($episode->EpisodeName)) {
                $parserEpisode->setEpisodeTitle((string)$episode->EpisodeName);
            } else {
                $parserEpisode->setEpisodeTitle($parserAnime->getTitle());
            }
            $parserEpisode->setPoster($this->downloadImage((string)$episode->filename));
            $parserEpisode->setRating((string)$episode->Rating);
            $parserEpisode->setViews(0);
            $parserEpisode->setComments(0);
            $parserEpisode->setAirDate((string)$episode->FirstAired);
            $parserEpisode->setRatingCount((string)$episode->RatingCount);
            $parserEpisode->setSummary((string)$episode->Overview);
            $parserEpisode->setImdbId((string)$episode->IMDB_ID);
            $parserEpisode->setAbsoluteNumber((int)$episode->absolute_number);

            $parserSeason->setEpisode($parserEpisode);

        }
        foreach ($seasons as $season) {
            $parserAnime->setSeason($season);
        }

        return $parserAnime;
    }

    /**
     * Download and returns a internal link to the image location
     * @param string $image String of image position on the api
     * @return string Internal Link to the image
     */
    private function downloadImage($image) {

        if ($image == "")
            return "";

        // Construct the download link
        $download_link = $this->makeLink(self::LINK_IMAGES, "", $image);

        // Checks if the path already exists or create one
        $path = dirname($this->rootDir) . "/web/img/episodes/thetvdb/" . $image;
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        // Output Path
        $output = $path;

        // Check if the image as been already downloaded, download it if it wasnt
        if (!file_exists($output))
            file_put_contents($output, file_get_contents($download_link));


        // Construct a valid address for the img
        $output = '/img/episodes/thetvdb/' . $image;

        return $output;
    }

    /**
     * @param string $link
     * @param string $animeKey
     * @param string $filename
     * @return string
     */
    private function makeLink($link, $animeKey, $filename = "") {
        return str_replace(["{api_key}", "{anime_key}", "{filename}"], [$this->apiKey, $animeKey, $filename], (string)$link);
    }
}

