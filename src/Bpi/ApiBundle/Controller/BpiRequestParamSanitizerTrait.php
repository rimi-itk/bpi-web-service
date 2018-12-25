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
     * @param array $input Input values.
     * @param array $required Required values.
     */
    protected function checkParams(array $input, array &$required)
    {
        array_walk_recursive(
            $input,
            function ($i, $k) use (&$required) {
                if (in_array($k, array_keys($required)) && !empty($i)) {
                    $required[$k]++;
                }
            }
        );
    }

    /**
     * Strips all params.
     *
     * @param array $input Input values.
     */
    protected function stripParams(array &$input)
    {
        array_walk_recursive(
            $input,
            function (&$i, $k) {
                $i = htmlspecialchars($i, ENT_QUOTES, 'UTF-8');
            }
        );
    }
}
