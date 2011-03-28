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

use Assetic\Filter\OptiPngFilter as BaseOptiPngFilter;

/**
 * Enables the Twig optipng() function.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class OptiPngFilter extends BaseOptiPngFilter implements TwigFunction
{
    static public function getTwigFunctionName()
    {
        return 'optipng';
    }

    static public function callFromTwig($input, array $options = array())
    {
    }
}
