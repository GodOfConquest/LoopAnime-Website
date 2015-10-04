<?php

namespace LoopAnime\AppBundle\Tests\Crawler\Guesser;

use LoopAnime\AppBundle\Crawler\Guesser\UrlGuesser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UrlGuesserTest extends KernelTestCase
{

    /**
     * @test
     */
    public function should_return_matchs()
    {
        $content = "asdasd asda dasd ad asd asdsad asdasd <a href='http://www.some.com/'>aasdasd</a> asdasd:
<a href='http://www.some.com/episode1'>episode 1</a>
<a href='http://www.some.com/episode2'>episode 2</a>
<a href='http://www.some.com/episode3'>episode 3</a>
<a href='http://www.some.com/episode4'>episode 4</a>
<a href='http://www.some.com/episode5'>episode 5</a>";
        $guesser = new UrlGuesser($content, ['episode1'], "http://www.some.com");
        $guesser->guess();
        $this->assertEquals(true, $guesser->isExactMatch());
        $this->assertEquals('http://www.some.com/episode1', $guesser->getUri());
        $this->assertEquals('episode1 === episode1 (100)', $guesser->getLog());
    }

    /**
     * @test
     */
    public function should_not_be_extact_match_when_not_found()
    {
        $content = "asdasd asda dasd ad asd asdsad asdasd <a href='http://www.some.com/'>aasdasd</a> asdasd:
<a href='http://www.some.com/episode1'>episode 1</a>
<a href='http://www.some.com/episode2'>episode 2</a>
<a href='http://www.some.com/episode3'>episode 3</a>
<a href='http://www.some.com/episode4'>episode 4</a>
<a href='http://www.some.com/episode5'>episode 5</a>";
        $guesser = new UrlGuesser($content, ['episode8'], "http://www.some.com");
        $guesser->guess();
        $this->assertEquals(false, $guesser->isExactMatch());
    }

    /**
     * @test
     */
    public function should_be_able_to_parse_special_chars()
    {
        $content = "asdasd asda dasd ad asd asdsad asdasd <a href='http://www.some.com/'>aasdasd</a> asdasd:
<a href='http://www.some.com/episode-very-special'>episode 1</a>
<a href='http://www.some.com/episode2'>episode 2</a>
<a href='http://www.some.com/episode3'>episode 3</a>
<a href='http://www.some.com/episode4'>episode 4</a>
<a href='http://www.some.com/episode5'>episode 5</a>";
        $guesser = new UrlGuesser($content, ['Episode% very% Special'], "http://www.some.com");
        $guesser->guess();
        $this->assertEquals(true, $guesser->isExactMatch());
    }

    /**
     * @test
     */
    public function should_be_able_get_uri()
    {
        $content = "asdasd asda dasd ad asd asdsad asdasd <a href='http://www.some.com/'>aasdasd</a> asdasd:
<a href='http://www.some.com/episode-very-special'>episode 1</a>
<a href='http://www.some.com/episode2'>episode 2</a>
<a href='http://www.some.com/episode3'>episode 3</a>
<a href='http://www.some.com/episode4'>episode 4</a>
<a href='http://www.some.com/episode5'>episode 5</a>";
        $guesser = new UrlGuesser($content, ['Episode% very% Special'], "http://www.some.com");
        $guesser->guess();
        $this->assertEquals("http://www.some.com/episode-very-special", $guesser->getUri());
    }

    /**
     * @test
     */
    public function should_be_able_to_grab_using_removal()
    {
        $content = '<ul>
      <a href="http://www.animenova.org/naruto-shippuuden-142-naruto-shippuuden-episode-142">Naruto Shippuuden Episode 142</a>
          </li>
      <li>
          </li>
    </ul>';
        $guesser = new UrlGuesser($content, ['naruto-shippuuden-142'], "http://www.animenova.org");
        $guesser->guess(['episode']);
        $this->assertEquals("http://www.animenova.org/naruto-shippuuden-142-naruto-shippuuden-episode-142", $guesser->getUri());
    }

    /**
     * @test
     */
    public function test_this()
    {
        $content = '<li>
      <a href="http://www.animenova.org/naruto-shippuuden-53-54-naruto-shippuuden-53-54">Naruto Shippuuden Episode 53-54</a>
          </li>';
        $guesser = new UrlGuesser($content, ['naruto-shippuuden-53-54'], "http://www.animenova.org");
        $guesser->guess(['episode']);
        $this->assertEquals("http://www.animenova.org/naruto-shippuuden-53-54-naruto-shippuuden-53-54", $guesser->getUri());
    }

}
