<?php
namespace LoopAnime\AppBundle\Utility;


class StringUtility
{

    public static function cleanStringUrlMatcher($string, $removal = [])
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '-', $string); // Replace special chars per -
        foreach ($removal as $remove) {
            $string = str_replace($remove, "", $string);
        }
        $string = implode("-", array_filter(array_unique(explode("-", $string)))); // Removes duplicated, empty entries

        return trim(strtolower($string));
    }

}
