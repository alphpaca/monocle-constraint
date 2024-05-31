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

namespace Alphpaca\Monocle\Constraint;

use Alphpaca\Monocle\Constraint\Filter\PackagesFilter;
use Alphpaca\Monocle\Constraint\Map\MonorepositoryPackagesMap;
use Alphpaca\Monocle\Constraint\Map\PackageConstraintMap;
use Alphpaca\Monocle\Constraint\Resolver\PackageReplacementsResolver;
use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PrePoolCreateEvent;
use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Semver\VersionParser;
use Symfony\Component\HttpClient\HttpClient;

final class Plugin implements PluginInterface, EventSubscriberInterface
{
    private readonly PackageReplacementsResolver $packageReplacementsResolver;

    private readonly VersionParser $versionParser;

    private ?IOInterface $io = null;

    private ?Composer $composer = null;

    /** @var array<MonorepositoryPackagesMap> */
    private array $monorepositoriesPackagesMaps = [];

    public function __construct(
        ?PackageReplacementsResolver $packageReplacementsResolver = null,
        ?VersionParser $versionParser = null,
    ) {
        $this->packageReplacementsResolver = $packageReplacementsResolver ?? new PackageReplacementsResolver(HttpClient::create());
        $this->versionParser = $versionParser ?? new VersionParser();
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;

        $monorepositoriesConstraintsMap = $this->getMonocleComposerConfig('constraints');

        if (null === $monorepositoriesConstraintsMap || [] === $monorepositoriesConstraintsMap) {
            $io->debug('No monorepositories constraints found in composer.json');

            return;
        }

        /**
         * @var string $monorepository
         * @var string $constraint
         */
        foreach ($monorepositoriesConstraintsMap as $monorepository => $constraint) {
            [$vendorName, $packageName] = explode('/', $monorepository);
            /** @var ConstraintInterface $constraint */
            $constraint = $this->versionParser->parseConstraints($constraint);
            $monorepositoryPackagesMap = new MonorepositoryPackagesMap(
                $monorepository,
                $constraint,
            );

            $resolvedMonorepositoryPackages = $this->packageReplacementsResolver->resolveFor(
                $vendorName,
                $packageName,
                $constraint,
            );

            foreach ($resolvedMonorepositoryPackages as $package) {
                $monorepositoryPackagesMap->addPackage(
                    new PackageConstraintMap($package, $constraint),
                );
            }

            $this->monorepositoriesPackagesMaps[] = $monorepositoryPackagesMap;
        }
    }

    private function getMonocleComposerConfig(string $key): mixed
    {
        if (null === $this->composer) {
            throw new \RuntimeException('Composer instance is not set');
        }

        $composerExtra = $this->composer->getPackage()->getExtra();

        if (!array_key_exists('monocle', $composerExtra)) {
            return null;
        }

        $monocleConfig = $composerExtra['monocle'];

        return $monocleConfig[$key] ?? null;
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PluginEvents::PRE_POOL_CREATE => 'filterPackages',
        ];
    }

    public function filterPackages(PrePoolCreateEvent $event): void
    {
        if (null === $this->io) {
            throw new \RuntimeException('IO instance is not set');
        }

        $packageFilter = new PackagesFilter($this->versionParser);
        $restrictedPackages = [];

        foreach ($this->monorepositoriesPackagesMaps as $monorepositoryPackagesMap) {
            $this->io->write(
                sprintf(
                    'Restricting packages being a part of the "%s" monorepository to version "%s"',
                    $monorepositoryPackagesMap->monorepositoryName,
                    $monorepositoryPackagesMap->constraint->getPrettyString(),
                ),
            );

            $restrictedPackages = array_merge($restrictedPackages, $monorepositoryPackagesMap->getPackages());
        }

        $filteredPackages = $packageFilter->filter(
            $event->getPackages(),
            $restrictedPackages,
        );

        $event->setPackages($filteredPackages);
    }
}
