<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;

final class ArrayDestructVariableFilter
{
    public function __construct(
        private readonly SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private readonly NodeNameResolver $nodeNameResolver,
    ) {
    }

    /**
     * @param Assign[] $variableAssigns
     * @return Assign[]
     */
    public function filterOut(array $variableAssigns, ClassMethod $classMethod): array
    {
        $arrayDestructionCreatedVariables = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use (
            &$arrayDestructionCreatedVariables
        ) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $node->var instanceof Array_ && ! $node->var instanceof List_) {
                return null;
            }

            foreach ($node->var->items as $arrayItem) {
                // empty item
                if (! $arrayItem instanceof ArrayItem) {
                    continue;
                }

                if (! $arrayItem->value instanceof Variable) {
                    continue;
                }

                /** @var string $variableName */
                $variableName = $this->nodeNameResolver->getName($arrayItem->value);
                $arrayDestructionCreatedVariables[] = $variableName;
            }
        });

        return array_filter(
            $variableAssigns,
            fn (Assign $assign): bool => ! $this->nodeNameResolver->isNames(
                $assign->var,
                $arrayDestructionCreatedVariables
            )
        );
    }
}
