<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2011 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Test\Factory\Loader;

use Assetic\Factory\Loader\YamlFormulaLoader;
use Assetic\Factory\Resource\FileResource;

class YamlFormulaLoaderTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var YamlFormulaLoader
     */
    protected $loader;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        if (!class_exists('Symfony\Component\Yaml\Yaml'))
        {
            $this->markTestSkipped('Symfony\'s Yaml is not installed');
        }
        $this->loader = new YamlFormulaLoader();
    }

    public function testLoad() {
        $formulae = $this->loadYamlFile('formulae.yml');
        $this->assertEquals(2, count($formulae));
        $this->assertEquals(3, count($formulae['foo1']));
        $this->assertInternalType('array', $formulae['foo1'][0]);
        $this->assertInternalType('array', $formulae['foo1'][1]);
        $this->assertInternalType('array', $formulae['foo1'][2]);
        $this->assertEmpty($formulae['foo1'][1]);
        $this->assertEmpty($formulae['foo1'][2]);
        $this->assertEquals(array('bar'), $formulae['foo1'][0]);
        $this->assertEquals(array('bar1','bar2'), $formulae['foo2'][0]);
        $this->assertEquals(array('filter1','?filter2'), $formulae['foo2'][1]);
        $this->assertEquals(array('debug' => true, 'output' => 'css/*.css'), 
                $formulae['foo2'][2]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArrayNode()
    {
        $formulae = $this->loadYamlFile('formulae_invalid_array_node.yml');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidKey()
    {
        $formulae = $this->loadYamlFile('formulae_invalid_key.yml');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidSubNode()
    {
        $formulae = $this->loadYamlFile('formulae_invalid_sub_node.yml');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArrayOrStringNode()
    {
        $formulae = $this->loadYamlFile('formulae_invalid_array_or_string_node.yml');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testMissingRequired()
    {
        $formulae = $this->loadYamlFile('formulae_missing_required.yml');
    }
    
    protected function loadYamlFile($file)
    {
        $res = new FileResource(__DIR__.'/templates/'.$file);
        return $this->loader->load($res);
    }
}

?>
