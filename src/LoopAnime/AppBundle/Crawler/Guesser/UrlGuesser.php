<?php

namespace LoopAnime\AppBundle\Crawler\Guesser;

use LoopAnime\AppBundle\Crawler\Enum\GuessTypesEnum;
use LoopAnime\AppBundle\Utility\StringUtility;

class UrlGuesser extends AbstractGuesser implements GuesserInterface
{

    protected $bestMatch;

    public function __construct($content, $titles, $domain)
    {
        $this->content = $content;
        $this->titles = !is_array($titles) ? [$titles] : $titles;
        $this->bestMatch = ['type' => GuessTypesEnum::NO_MATCH, 'percentage' => 0];
        $this->domain = $domain;
    }

    public function guess($removal = [])
    {
        $matches = [];
        preg_match_all("/href=[\"'](.*?)[\"']/mi", $this->content, $matches);

        foreach ($this->titles as $title) {
            $match1 = StringUtility::cleanStringUrlMatcher($title, $removal);
            foreach ($matches[1] as $key => $uri) {
                $match = basename($uri);
                $match2 = StringUtility::cleanStringUrlMatcher($match, $removal);
                similar_text($match1, $match2, $percentage);
                if (strpos($uri, "http") === false) {
                    $uri = $this->domain . $uri;
                }
                if ($this->bestMatch['percentage'] < $percentage) {
                    $this->bestMatch = [
                        'type' => GuessTypesEnum::BEST_MATCH,
                        'match1' => $match1,
                        'match2' => $match2,
                        'percentage' => $percentage,
                        'uri' => $uri
                    ];
                }
                if ($match1 === $match2 || $percentage == 100) {
                    $this->bestMatch['type'] = GuessTypesEnum::EXACT_MATCH;
                    return $this->bestMatch;
                }
            }
        }
        return $this->bestMatch;
    }

}
