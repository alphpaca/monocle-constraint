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

namespace Tests\Alphpaca\Monocle\Constraint\WhiteBox\Filter;

use Alphpaca\Monocle\Constraint\Filter\PackagesFilter;
use Alphpaca\Monocle\Constraint\Map\PackageConstraintMap;
use Composer\Package\CompletePackage;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Constraint\MultiConstraint;
use Composer\Semver\VersionParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PackageFilterTest extends TestCase
{
    #[Test]
    public function it_filters_packages(): void
    {
        $packages = [
            new CompletePackage('sylius/core-bundle', '2.0.0', '2.0.0.0'),
            new CompletePackage('sylius/core-bundle', '1.2.0', '1.2.0.0'),
            new CompletePackage('sylius/core-bundle', '1.1.0', '1.1.0.0'),
            new CompletePackage('sylius/core-bundle', '1.0.0', '1.0.0.0'),
            new CompletePackage('symfony/yaml', '6.4.0', '6.4.0.0'),
            new CompletePackage('symfony/yaml', '6.3.1', '6.3.1.0'),
            new CompletePackage('symfony/yaml', '6.3.0', '6.3.0.0'),
        ];

        $knownPackages = [
            'sylius/core-bundle' => new PackageConstraintMap('sylius/core-bundle', new MultiConstraint([
                new Constraint('>=', '1.0.0'),
                new Constraint('<', '2.0.0'),
            ])),
            'symfony/yaml' => new PackageConstraintMap('symfony/yaml', new MultiConstraint([
                new Constraint('>=', '6.3.0'),
                new Constraint('<', '6.4.0'),
            ])),
        ];

        $packagesFilter = new PackagesFilter(new VersionParser());

        $filteredPackages = $packagesFilter->filter($packages, $knownPackages);

        $this->assertCount(5, $filteredPackages);
        $this->assertFalse(in_array($packages[0], $filteredPackages, true));
        $this->assertFalse(in_array($packages[4], $filteredPackages, true));
    }
}
