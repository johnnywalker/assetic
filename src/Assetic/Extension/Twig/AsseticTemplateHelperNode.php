<?php

namespace Assetic\Extension\Twig;

class AsseticTemplateHelperNode extends \Twig_Node
{
    public function __construct($type, $template, $var_name, \Twig_Node_Expression_Array $args = null, $lineno = 0, $tag = null)
    {
        $nodes = array('args' => (is_null($args) ? new \Twig_Node_Expression_Array(array(), $lineno) : $args));
        $attributes = array(
            'type'          => $type, 
            'template'      => $template, 
            'var_name'      => $var_name,
        );

        if (!ctype_alnum(static::camelize($type)))
            throw new \Twig_Error(sprintf('Invalid characters in asset type: "%s".', $type), $lineno);
        if (!ctype_alnum(static::camelize($template)))
            throw new \Twig_Error(sprintf('Invalid characters in asset template name: "%s".', $template), $lineno);

        parent::__construct($nodes, $attributes, $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $func = sprintf(
            "get%s",
            static::getTemplateMacroName(
                $this->getAttribute('type'),
                $this->getAttribute('template')
            )
        );


        $compiler
            ->addDebugInfo($this)
            ->write("\$obj = \$this;\n")
            ->write("while (false !== \$obj && false === method_exists(\$obj, ")
            ->repr($func)
            ->raw(")) {\n")
            ->indent()
            ->write("\$obj = \$obj->getParent(\$context);\n")
            ->outdent()
            ->write("}\n")
            ->write("if (false === \$obj) {\n")
            ->indent()
            ->write(sprintf(
                "throw new \Twig_Error_Runtime('Assetic template \"%s\" for type \"%s\" not found.');\n",
                $this->getAttribute('template'),
                $this->getAttribute('type')
            ))
            ->outdent()
            ->write("}\n")
            ->write(sprintf("echo \$obj->%s(\$context[", $func))
            ->repr($this->getAttribute('var_name'))
            ->raw("], ")
            ->subcompile($this->getNode('args'))
            ->raw(");\n")
            ->write("unset(\$obj);\n");
    }

    static public function camelize($string) 
    { 
      return str_replace(' ', '', ucwords(str_replace(array('-', '_'), ' ', $string))); 
    } 

    static public function getTemplateMacroName($type, $name)
    {
        return sprintf(
            "Assetic%sTemplate%s",
            static::camelize($type),
            static::camelize($name)
        );
    }
}
