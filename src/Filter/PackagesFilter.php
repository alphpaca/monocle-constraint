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

namespace Alphpaca\Monocle\Constraint\Filter;

use Alphpaca\Monocle\Constraint\Map\PackageConstraintMap;
use Composer\Package\BasePackage;
use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Semver\VersionParser;

class PackagesFilter
{
    /**
     * @param array<BasePackage>                  $packages
     * @param array<string, PackageConstraintMap> $knownPackages
     *
     * @return array<BasePackage>
     */
    public static function filter(array $packages, array $knownPackages, ConstraintInterface $constraint): array
    {
        $filteredPackages = [];

        foreach ($packages as $package) {
            $packageName = $package->getName();

            if (!array_key_exists($packageName, $knownPackages)) {
                $filteredPackages[] = $package;
                continue;
            }

            $packageVersion = (new VersionParser())->parseConstraints($package->getVersion());

            if (!$packageVersion->matches($constraint)) {
                continue;
            }

            $filteredPackages[] = $package;
        }

        return $filteredPackages;
    }
}
