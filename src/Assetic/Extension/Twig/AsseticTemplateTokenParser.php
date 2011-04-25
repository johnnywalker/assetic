<?php

namespace Assetic\Extension\Twig;

class AsseticTemplateTokenParser extends \Twig_TokenParser
{
    private $tag;

    public function __construct($tag)
    {
        $this->tag = $tag;
    }

    public function parse(\Twig_Token $token)
    {
        $attributes = array(
            'type'     => 'css',
            'name'     => 'default',
            'var_name' => 'asset_url',
        );
        $args = array(
            'required' => false,
            'optional' => false,
        );

        $stream = $this->parser->getStream();
        while(!$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
            if ($stream->test(\Twig_Token::STRING_TYPE)) {
                // 'default'
                $attributes['name'] = $stream->next()->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'type')) {
                // type='css'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $attributes['type'] = $stream->expect(\Twig_Token::STRING_TYPE, array('css', 'js', 'img'))->getValue();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'required')) {
                // required=['alt', 'title']
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $args['required'] = $this->parser->getExpressionParser()->parseArrayExpression();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'optional')) {
                // optional=['alt', 'title']
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $args['optional'] = $this->parser->getExpressionParser()->parseArrayExpression();
            } elseif ($stream->test(\Twig_Token::NAME_TYPE, 'var_name')) {
                // var_name='asset_url'
                $stream->next();
                $stream->expect(\Twig_Token::OPERATOR_TYPE, '=');
                $attributes['var_name'] = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
            } else {
                $token = $stream->getCurrent();
                throw new \Twig_Error_Syntax(sprintf('Unexpected token "%s" of value "%s"', \Twig_Token::typeToEnglish($token->getType(), $token->getLine()), $token->getValue()), $token->getLine());
            }
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $endtag = 'end'.$this->getTag();
        $test = function(\Twig_Token $token) use($endtag) { return $token->test($endtag); };
        $this->parser->pushLocalScope();
        $body = $this->parser->subparse($test, true);
        $this->parser->popLocalScope();

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        if (!ctype_alnum(AsseticTemplateHelperNode::camelize($attributes['name']))) {
            throw new \Twig_Error_Syntaz(sprintf('Invalid characters in assetic template name: "%s".', $attributes['name']));
        }

        if (!$args['required'])
            $args['required'] = new \Twig_Node_Expression_Array(array(), $token->getLine());
        if (!$args['optional'])
            $args['optional'] = new \Twig_Node_Expression_Array(array(), $token->getLine());

        if (!is_array($body))
            $body = array($body);

        $nodes = array();
        $nodes['template_args'] = new AsseticTemplateArgumentsNode($args['required'], $args['optional'], $token->getLine());
        foreach ($body as $key => $node)
        {
            $nodes[$key] = $node;
        }
        $body = new \Twig_Node($nodes, array(), $token->getLine(), $this->getTag());


        $templateMacroName = AsseticTemplateHelperNode::getTemplateMacroName($attributes['type'], $attributes['name']);
        $arguments = new \Twig_Node(array(
            new \Twig_Node_Expression_Name($attributes['var_name'], $token->getLine()),
            new \Twig_Node_Expression_Name('_assetic_template_args', $token->getLine()),
        ), array(), $token->getLine(), $this->getTag());
        $this->parser->setMacro(
            $templateMacroName,
            new \Twig_Node_Macro($templateMacroName, $body, $arguments, $token->getLine(), $this->getTag())
        );

        return null;
    }

    public function getTag()
    {
        return $this->tag;
    }
}
