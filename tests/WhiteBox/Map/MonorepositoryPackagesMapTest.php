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

use Alphpaca\Monocle\Constraint\Map\MonorepositoryPackagesMap;
use Alphpaca\Monocle\Constraint\Map\PackageConstraintMap;
use Composer\Semver\Constraint\Constraint;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MonorepositoryPackagesMapTest extends TestCase
{
    #[Test]
    public function it_returns_its_name(): void
    {
        $map = new MonorepositoryPackagesMap('alphpaca/monocle', new Constraint('>=', '1.0.0'));

        $this->assertSame('alphpaca/monocle', $map->monorepositoryName);
    }

    #[Test]
    public function it_returns_its_packages(): void
    {
        $map = new MonorepositoryPackagesMap('alphpaca/monocle', new Constraint('>=', '1.0.0'));
        $map->addPackage(new PackageConstraintMap('alphpaca/monocle-constraint', '>=1.0.0'));
        $map->addPackage(new PackageConstraintMap('alphpaca/monocle-crossroad', '>=1.0.0'));

        $this->assertCount(2, $map->getPackages());
        $this->assertSame('alphpaca/monocle-constraint', $map->getPackages()['alphpaca/monocle-constraint']->packageName);
        $this->assertSame('alphpaca/monocle-crossroad', $map->getPackages()['alphpaca/monocle-crossroad']->packageName);
    }
}
