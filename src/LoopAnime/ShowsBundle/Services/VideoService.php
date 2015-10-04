<?php

namespace LoopAnime\ShowsBundle\Services;

use LoopAnime\AppBundle\Crawler\Service\CrawlerService;
use LoopAnime\ShowsBundle\Entity\AnimesLinks;

class VideoService
{

    private $hq;
    private $lq;
    private $dq;

    public function __construct(CrawlerService $crawlerService)
    {
        $this->crawlerService = $crawlerService;
    }

    public function getDirectVideoLink(AnimesLinks $link)
    {
        $hoster = $this->crawlerService->getHoster($link->getHoster());
        if ($hoster->isIframe()) {
            return [];
        }
        $hoster->getDirectLinks($link->getLink());

        return $hoster->getDirectLinks($link->getLink());
    }

    public function getHQVideoLink()
    {
        return $this->hq;
    }

    public function hasHQVideoLink()
    {
        if (!empty($this->hq)) {
            return true;
        } else {
            return false;
        }
    }

    public function getLQVideoLink()
    {
        return $this->lq;
    }

    public function getDQVideoLink()
    {
        return $this->dq;
    }

    public function getIframeLink(AnimesLinks $link)
    {
        $link = $link->getLink();
        $link = parse_url($link);
        $query = !empty($link['query']) ? explode("&", $link['query']) : [];
        foreach ($query as &$fragment) {
            list($key, $value) = explode("=", $fragment);
            if ($key === "w" || $key === "width") {
                $fragment = $key . "=550px";
            }
            if ($key === "h" || $key === "height") {
                $fragment = $key . '=300px';
            }
        }
        return $link['scheme'] . "://" . $link['host'] . $link['path'] . '?' . implode("&", $query);
    }

}
