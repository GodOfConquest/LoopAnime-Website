<?php
namespace LoopAnime\AppBundle\Tests\Utility;


use LoopAnime\AppBundle\Utility\StringUtility;

class StringUtilityTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function can_clean_string()
    {
        $string = "Hello";
        $this->assertEquals("hello", StringUtility::cleanStringUrlMatcher($string));
    }

    /**
     * @test
     */
    public function can_clean_string_with_special_chars()
    {
        $string = "hello%world";
        $this->assertEquals("hello-world", StringUtility::cleanStringUrlMatcher($string));
    }

    /**
     * @test
     */
    public function can_clean_string_with_spaces()
    {
        $string = "Hello world";
        $this->assertEquals("hello-world", StringUtility::cleanStringUrlMatcher($string));
    }

    /**
     * @test
     */
    public function can_clean_string_wtih_spaces_and_specials()
    {
        $string = "Hello% world";
        $this->assertEquals("hello-world", StringUtility::cleanStringUrlMatcher($string));
    }

    /**
     * @test
     */
    public function can_clean_strings_with_accents()
    {
        $string = "Hello é world";
        $this->assertEquals("hello-world", StringUtility::cleanStringUrlMatcher($string));
    }
}
