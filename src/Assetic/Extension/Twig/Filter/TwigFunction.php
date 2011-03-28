<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2011 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Extension\Twig\Filter;

/**
 * Implemented by filters that want to expose Twig functions.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
interface TwigFunction
{
    /**
     * Returns the name of the filter's Twig function.
     *
     * @return string A valid function name
     */
    static function getTwigFunctionName();

    /**
     * Handles a call to the filter from Twig.
     *
     * @param string $input   A single input string
     * @param array  $options An array of filter options
     *
     * @return string The created asset's target URL
     */
    static function callFromTwig($input, array $options = array());
}
