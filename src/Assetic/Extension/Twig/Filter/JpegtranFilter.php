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

use Assetic\Filter\JpegtranFilter as BaseJpegtranFilter;

/**
 * Enables the Twig jpegtran() function.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class JpegtranFilter extends BaseJpegtranFilter implements TwigFunction
{
    static public function getTwigFunctionName()
    {
        return 'jpegtran';
    }

    static public function callFromTwig($input, array $options = array())
    {
    }
}
