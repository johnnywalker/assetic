<?php

namespace Assetic\Extension\Twig;

class AsseticTemplateArgumentsNode extends \Twig_Node
{
    public function __construct(\Twig_Node_Expression_Array $required, \Twig_Node_Expression_Array $optional, $lineno = 0)
    {
        parent::__construct(array('required' => $required, 'optional' => $optional), array(), $lineno);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("\$required = ")
            ->subcompile($this->getNode('required'))
            ->raw(";\n")
            ->write("\$optional = ")
            ->subcompile($this->getNode('optional'))
            ->raw(";\n")
            ->write("\$missing = array_diff(\$required, array_keys(\$context['_assetic_template_args']));\n")
            ->write("\$unknown = array_diff(array_keys(\$context['_assetic_template_args']), \$required, \$optional);\n")
            ->write("if (0 < count(\$missing)) {\n")
            ->indent()
            ->write("throw new \Twig_Error_Runtime(sprintf('Required template arguments missing: %s', implode(', ', \$missing)));\n")
            ->outdent()
            ->write("}\n")
            ->write("if (0 < count(\$unknown)) {\n")
            ->indent()
            ->write("throw new \Twig_Error_Runtime(sprintf('Unexpected template arguments: %s', implode(', ', \$unknown)));\n")
            ->outdent()
            ->write("}\n")
            ->write("\$context = array_merge(\$context, \$context['_assetic_template_args']);\n")
            ->write("unset(\$context['_assetic_template_args']);\n");
    }
}
