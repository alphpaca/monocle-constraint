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

final readonly class MonorepositoryPackagesMap
{
    /**
     * @param array<PackageConstraintMap> $packages
     */
    public function __construct(
        public string $monorepositoryName,
        public array $packages,
    ) {
        foreach ($packages as $package) {
            if (!$package instanceof PackageConstraintMap) {
                throw new \InvalidArgumentException('All packages must be an instance of '.PackageConstraintMap::class);
            }
        }
    }
}
