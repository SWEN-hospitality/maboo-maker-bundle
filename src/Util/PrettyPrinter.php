<?php

declare(strict_types=1);

namespace Bornfight\MabooMakerBundle\Util;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\PrettyPrinter\Standard;

class PrettyPrinter extends Standard
{
    /**
     * Overridden to fix indentation problem with tabs.
     *
     * If the original source code uses tabs, then the tokenizer
     * will see this as "1" indent level, and will indent new lines
     * with just 1 space. By changing 1 indent to 4, we effectively
     * "correct" this problem when printing.
     *
     * For code that is even further indented (e.g. 8 spaces),
     * the printer uses the first indentation (here corrected
     * from 1 space to 4) and already (without needing any other
     * changes) adds 4 spaces onto that. This is why we don't
     * also need to handle indent levels of 5, 9, etc: these
     * do not occur (at least in the code we generate);
     */
    protected function setIndentLevel(int $level): void
    {
        if (1 === $level) {
            $level = 4;
        }

        parent::setIndentLevel($level);
    }

    /**
     * Overridden to change coding standards.
     *
     * Before:
     *      public function getFoo() : string
     *
     * After
     *      public function getFoo(): string
     */
    protected function pStmt_ClassMethod(Stmt\ClassMethod $node)
    {
        $classMethod = parent::pStmt_ClassMethod($node);

        if ($node->returnType) {
            $classMethod = str_replace(') :', '):', $classMethod);
        }

        return $classMethod;
    }

    /**
     * Overridden to change how attributes are displayed for params (not inline)
     */
    protected function pParam(Node\Param $node): string
    {
        return $this->pAttrGroups($node->attrGroups)
            . $this->pModifiers($node->flags)
            . ($node->type ? $this->p($node->type) . ' ' : '')
            . ($node->byRef ? '&' : '')
            . ($node->variadic ? '...' : '')
            . $this->p($node->var)
            . ($node->default ? ' = ' . $this->p($node->default) : '');
    }

    protected function isMultiline(array $nodes): bool
    {
        // By default, we want multiline
        if (count($nodes) < 2) {
            return true;
        }

        return parent::isMultiline($nodes);
    }
}
