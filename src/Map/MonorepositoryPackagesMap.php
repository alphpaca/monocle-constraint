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

namespace Alphpaca\Monocle\Constraint\Map;

use Composer\Semver\Constraint\ConstraintInterface;

final class MonorepositoryPackagesMap
{
    /** @var array<PackageConstraintMap> */
    private array $packages = [];

    public function __construct(
        public string $monorepositoryName,
        public ConstraintInterface $constraint,
    ) {
    }

    public function addPackage(PackageConstraintMap $package): void
    {
        $this->packages[$package->packageName] = $package;
    }

    /**
     * @return array<PackageConstraintMap>
     */
    public function getPackages(): array
    {
        return $this->packages;
    }
}
