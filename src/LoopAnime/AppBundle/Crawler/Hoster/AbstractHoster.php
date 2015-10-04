<?php
namespace LoopAnime\AppBundle\Crawler\Hoster;


use LoopAnime\AppBundle\Crawler\Exception\MalformedHosterException;

abstract class AbstractHoster implements HosterInterface
{

    protected $page;
    protected $lastPageContent;
    protected $searchLink;

    public function recreatePageParameter($link, $page)
    {
        $pageParameter = $this->getPageParameter();
        if (!$pageParameter) {
            throw new MalformedHosterException('Page Parameter is required on the hoster ' . get_class($this));
        }
        $link = preg_replace("/^.+([&?]$pageParameter=.+)[\\b&]/", "", $link);
        if (strpos(basename($link), "?") !== false) {
            return $link . "?$pageParameter=" . $page;
        } else {
            return $link . "&$pageParameter=" . $page;
        }
    }

    public function getNextPage($link, $page)
    {
        if ($this->isPaginated()) {
            return $this->recreatePageParameter($link, $page);
        }
        return $link;
    }

    public function search($searchTerm)
    {
        $searchTerm = urlencode($searchTerm);
        return str_replace('{search_term}', $searchTerm, $this->searchLink);
    }

    public function isPaginated()
    {
        return true;
    }

    public function isIframe()
    {
        return false;
    }

    public function getPageParameter()
    {
        if (!empty($this->pageParameter)) {
            return $this->pageParameter;
        }
        return false;
    }

    public function getDomain()
    {
        $url = parse_url($this->searchLink);
        return $url['scheme'] . "://" . $url['host'];
    }

}
