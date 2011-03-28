<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2011 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Extension\Twig;

use Assetic\Factory\AssetFactory;
use Assetic\Extension\Twig\Filter\TwigFunction;

class AsseticExtension extends \Twig_Extension
{
    protected $factory;
    protected $debug;

    public function __construct(AssetFactory $factory, $debug = false)
    {
        $this->factory = $factory;
        $this->debug = $debug;
    }

    public function getTokenParsers()
    {
        return array(
            new AsseticTokenParser($this->factory, 'javascripts', 'js/*.js', $this->debug),
            new AsseticTokenParser($this->factory, 'stylesheets', 'css/*.css', $this->debug),
            new AsseticTokenParser($this->factory, 'image', 'images/*', $this->debug, true),
        );
    }

    /**
     * Creates Twig functions for filters in the factory's filter manager.
     *
     * The filter must implement TwigFunction in order for a Twig
     * function to be created.
     */
    public function getFunctions()
    {
        $functions = array();

        if ($fm = $this->factory->getFilterManager()) {
            foreach ($fm->getNames() as $name) {
                $filter = $this->fm->get($name);
                if ($filter instanceof TwigFunction) {
                    $class = get_class($filter);
                    $functions[$class::getTwigFunctionName()] = new \Twig_Function_Function($class.'::callFromTwig');
                }
            }
        }

        return $functions;
    }

    public function getName()
    {
        return 'assetic';
    }
}
