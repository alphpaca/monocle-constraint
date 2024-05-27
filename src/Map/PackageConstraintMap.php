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
use Composer\Semver\VersionParser;

final readonly class PackageConstraintMap
{
    public ConstraintInterface $constraint;

    public function __construct(
        public string $packageName,
        string|ConstraintInterface $constraint,
    ) {
        $this->constraint = $constraint instanceof ConstraintInterface ? $constraint : (new VersionParser())->parseConstraints($constraint);
    }
}
