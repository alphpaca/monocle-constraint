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

namespace Alphpaca\Monocle\Constraint\Resolver;

use Alphpaca\Monocle\Constraint\Resolver\Exception\PackageResolvingException;
use Composer\MetadataMinifier\MetadataMinifier;
use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Semver\Semver;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PackageReplacementsResolver
{
    /**
     * @return array<string>
     *
     * @throws PackageResolvingException
     */
    public function resolveFor(string $vendorName, string $packageName, ConstraintInterface $constraint): array
    {
        try {
            /** @var array{replace?: array<string>} $package */
            $package = $this->getPackageMatchingConstraint($vendorName, $packageName, $constraint);
        } catch (HttpExceptionInterface|TransportExceptionInterface $e) {
            throw new PackageResolvingException($e->getMessage(), $e->getCode(), $e);
        }

        return array_keys($package['replace'] ?? []);
    }

    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws PackageResolvingException
     */
    private function getPackageMatchingConstraint(string $vendorName, string $packageName, ConstraintInterface $constraint): array
    {
        $content = $this->httpClient->request(
            'GET',
            sprintf('https://repo.packagist.org/p2/%s/%s.json', $vendorName, $packageName),
        )->getContent();

        $fullPackageName = sprintf('%s/%s', $vendorName, $packageName);

        $content = json_decode($content, true);
        $packages = MetadataMinifier::expand($content['packages'][$fullPackageName]);

        foreach ($packages as $package) {
            if (Semver::satisfies($package['version_normalized'], $constraint->getPrettyString())) {
                return $package;
            }
        }

        throw new PackageResolvingException(sprintf('No package matching constraint "%s" found for package "%s"', $constraint->getPrettyString(), $packageName));
    }
}
