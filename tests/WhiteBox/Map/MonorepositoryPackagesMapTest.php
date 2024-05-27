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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MonorepositoryPackagesMapTest extends TestCase
{
    #[Test]
    public function it_returns_its_name(): void
    {
        $map = new MonorepositoryPackagesMap('alphpaca/monocle', []);

        $this->assertSame('alphpaca/monocle', $map->monorepositoryName);
    }

    #[Test]
    public function it_returns_its_packages(): void
    {
        $packages = [
            new PackageConstraintMap('alphpaca/monocle-constraint', '>=1.0.0'),
            new PackageConstraintMap('alphpaca/monocle-crossroad', '>=1.0.0'),
        ];

        $map = new MonorepositoryPackagesMap('alphpaca/monocle', $packages);

        $this->assertCount(2, $map->packages);
        $this->assertSame($packages, $map->packages);
    }

    #[Test]
    public function it_throws_an_exception_if_any_package_is_not_an_instance_of_package_constraint_map(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All packages must be an instance of '.PackageConstraintMap::class);

        new MonorepositoryPackagesMap('alphpaca/monocle', [new \stdClass()]);
    }
}
