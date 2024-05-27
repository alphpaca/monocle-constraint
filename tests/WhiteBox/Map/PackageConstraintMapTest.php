<?php

declare(strict_types=1);

/*
 * This file is part of Alphpaca Monocle project (https://github.com/alphpaca/monocle).
 *
 * (c) Jacob Tobiasz <jacob@alphpaca.io>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tests\Alphpaca\Monocle\Constraint\WhiteBox\Map;

use Alphpaca\Monocle\Constraint\Map\PackageConstraintMap;
use Composer\Semver\Constraint\Constraint;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PackageConstraintMapTest extends TestCase
{
    #[Test]
    public function it_returns_the_package_name(): void
    {
        $map = new PackageConstraintMap('alphpaca/monocle-constraint', new Constraint('>=', '1.0.0'));

        $this->assertSame('alphpaca/monocle-constraint', $map->packageName);
    }

    #[Test]
    public function it_returns_the_constraint(): void
    {
        $map = new PackageConstraintMap('alphpaca/monocle-constraint', new Constraint('>=', '1.0.0'));

        $this->assertInstanceOf(Constraint::class, $map->constraint);
    }

    #[Test]
    public function it_converts_string_constraint_to_constraint_instance(): void
    {
        $map = new PackageConstraintMap('alphpaca/monocle-constraint', '>=1.0.0');

        $this->assertInstanceOf(Constraint::class, $map->constraint);
        $this->assertSame('>=1.0.0', $map->constraint->getPrettyString());
    }
}
