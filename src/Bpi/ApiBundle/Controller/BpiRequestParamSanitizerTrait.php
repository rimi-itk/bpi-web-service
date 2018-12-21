<?php
/**
 * @file
 *  Controller with helper methods
 */

namespace Bpi\ApiBundle\Controller;

/**
 * Trait BpiRequestParamSanitizerTrait.
 */
trait BpiRequestParamSanitizerTrait
{
    /**
     * Check if params are present and how many times.
     *
     * @param $input
     * @param $required
     */
    protected function checkParams($input, &$required)
    {
        array_walk_recursive($input, function($i, $k) use (&$required)  {
            if (in_array($k, array_keys($required)) && !empty($i)) {
                $required[$k]++;
            }
        });

    }

    /**
     * Strips all params.
     *
     * @param $input
     */
    protected function stripParams(&$input)
    {
        array_walk_recursive($input, function(&$i, $k) {
            $i = htmlspecialchars($i, ENT_QUOTES, 'UTF-8');
        });
    }
}
