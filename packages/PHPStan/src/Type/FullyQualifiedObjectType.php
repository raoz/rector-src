<?php

declare(strict_types=1);

namespace Rector\PHPStan\Type;

use Nette\Utils\Strings;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class FullyQualifiedObjectType extends ObjectType
{
    public function getShortNameType(): ShortenedObjectType
    {
        return new ShortenedObjectType($this->getShortName(), $this->getClassName());
    }

    public function areShortNamesEqual(self $compoaredFullyQualifiedObjectType): bool
    {
        return $this->getShortName() === $compoaredFullyQualifiedObjectType->getShortName();
    }

    public function getShortName(): string
    {
        if (! Strings::contains($this->getClassName(), '\\')) {
            return $this->getClassName();
        }

        return (string) Strings::after($this->getClassName(), '\\', -1);
    }

    public function getShortNameNode(): Name
    {
        return new Name($this->getShortName());
    }

    public function getUseNode(): Use_
    {
        $name = new Name($this->getClassName());
        $useUse = new UseUse($name);

        $name->setAttribute(AttributeKey::PARENT_NODE, $useUse);

        return new Use_([$useUse]);
    }

    public function getFunctionUseNode(): Use_
    {
        $name = new Name($this->getClassName());
        $useUse = new UseUse($name, null, Use_::TYPE_FUNCTION);

        $name->setAttribute(AttributeKey::PARENT_NODE, $useUse);

        return new Use_([$useUse]);
    }
}
