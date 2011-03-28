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

use Assetic\Filter\JpegoptimFilter as BaseJpegoptimFilter;

/**
 * Enables the Twig jpegoptim() function.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class JpegoptimFilter extends BaseJpegoptimFilter implements TwigFunction
{
    static public function getTwigFunctionName()
    {
        return 'jpegoptim';
    }

    static public function callFromTwig($input, array $options = array())
    {
    }
}
