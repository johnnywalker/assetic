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

use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;

class AsseticTokenParser extends \Twig_TokenParser
{
    private $factory;
    private $tag;
    private $output;
    private $single;
    private $extensions;
    private $generic;

    /**
     * Constructor.
     *
     * Attributes can be added to the tag by passing names as the options
     * array. These values, if found, will be passed to the factory and node.
     *
     * @param AssetFactory $factory    The asset factory
     * @param string       $tag        The tag name
     * @param string       $output     The default output string
     * @param Boolean      $single     Whether to force a single asset
     * @param array        $extensions Additional attribute names to look for
     * @param Boolean      $generic    Whether this is a generic asset - type & template used
     */
    public function __construct(AssetFactory $factory, $tag, $output, $single = false, array $extensions = array(), $generic = false)
    {
        $this->factory    = $factory;
        $this->tag        = $tag;
        $this->output     = $output;
        $this->single     = $single;
        $this->extensions = $extensions;
        $this->generic    = $generic;
    }

    public function parse(\Twig_Token $token)
    {
        $inputs = array();
        $filters = array();
        $name = null;
        $attributes = array(
            'output'   => $this->output,
            'var_name' => 'asset_url',
        );
        if ($this->generic) {
            $type     = 'css';
            $template = 'default';
            $args     = null;
        }

        $stream = $this->parser->getStream();
        while (!$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
            if ($stream->test(\Twig_Token::STRING_TYPE)) {
                // '@jquery', 'js/src/core/*', 'js/src/extra.js'
                $inputs[] = $stream->next()->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'filter')) {
                // filter='yui_js'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $filters = array_merge($filters, array_filter(array_map('trim', explode(',', $stream->expect(\Twig_Token::STRING_TYPE)->getValue()))));
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'output')) {
                // output='js/packed/*.js' OR output='js/core.js'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $attributes['output'] = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'name')) {
                // name='core_js'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $name = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'as')) {
                // as='the_url'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $attributes['var_name'] = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'debug')) {
                // debug=true
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $attributes['debug'] = 'true' == $stream->expect(\Twig_Token::NAME_TYPE, array('true', 'false'))->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'type') && $this->generic) {
                // type='css'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $type = $stream->expect(\Twig_Token::STRING_TYPE, array('css', 'js', 'img'))->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'template') && $this->generic) {
                // template='default'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $template = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'args') && $this->generic) {
                // args={'alt': 'this is an alt'}
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $args = $this->parser->getExpressionParser()->parseHashExpression();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, $this->extensions)) {
                // an arbitrary configured attribute
                $key = $stream->next()->getValue();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $attributes[$key] = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            } else {
                $token = $stream->getCurrent();
                throw new \Twig_Error_Syntax(sprintf('Unexpected token "%s" of value "%s"', \Twig_Token::typeToEnglish($token->getType(), $token->getLine()), $token->getValue()), $token->getLine());
            }
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        if ($this->generic) {
            $body = new AsseticTemplateHelperNode($type, $template, $attributes['var_name'],
                $args, $token->getLine(), $this->getTag());
        } else {
            $endtag = 'end'.$this->getTag();
            $test = function(\Twig_Token $token) use($endtag) { return $token->test($endtag); };
            $body = $this->parser->subparse($test, true);

            $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        }

        if ($this->single && 1 < count($inputs)) {
            $inputs = array_slice($inputs, -1);
        }

        if (!$name) {
            $name = $this->factory->generateAssetName($inputs, $filters);
        }

        $asset = $this->factory->createAsset($inputs, $filters, $attributes + array('name' => $name));

        return $this->createNode($asset, $body, $inputs, $filters, $name, $attributes, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return $this->tag;
    }

    protected function createNode(AssetInterface $asset, \Twig_NodeInterface $body, array $inputs, array $filters, $name, array $attributes = array(), $lineno = 0, $tag = null)
    {
        return new AsseticNode($asset, $body, $inputs, $filters, $name, $attributes, $lineno, $tag);
    }
}
