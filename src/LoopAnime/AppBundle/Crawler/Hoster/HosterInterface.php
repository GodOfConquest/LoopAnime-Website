<?php
namespace LoopAnime\AppBundle\Crawler\Hoster;


interface HosterInterface
{

    public function search($searchTerm);

    public function getEpisodeMirrors($link);

    public function getDirectLinks($link);

    public function getNextPage($link, $page);

    public function isIframe();

    public function getPageParameter();

    public function getSubtitles();

    public function getStrategy();

    public function getDomain();

    public function isPaginated();

    public function getName();

}
