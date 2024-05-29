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

namespace Tests\Alphpaca\Monocle\Constraint\WhiteBox\Resolver;

use Alphpaca\Monocle\Constraint\Resolver\Exception\PackageResolvingException;
use Alphpaca\Monocle\Constraint\Resolver\PackageReplacementsResolver;
use Composer\Semver\Constraint\Constraint;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class PackageReplacementsResolverTest extends TestCase
{
    #[Test]
    public function it_resolves_package_replacements(): void
    {
        /** @var string $body */
        $body = file_get_contents(dirname(__DIR__).'/.fixtures/packagist_api_responses/example_200.json');

        $response = new MockResponse($body);
        $httpClient = new MockHttpClient([$response, $response]);

        $resolver = new PackageReplacementsResolver($httpClient);
        $resolvedForHigherVersion = $resolver->resolveFor('alphpaca', 'stack', new Constraint('>=', '2.0.0'));
        $resolvedForLowerVersion = $resolver->resolveFor('alphpaca', 'stack', new Constraint('<', '2.0.0'));

        $this->assertSame(
            [
                'alphpaca/admin',
                'alphpaca/api',
                'alphpaca/resource',
            ],
            $resolvedForHigherVersion,
        );
        $this->assertSame(
            [
                'alphpaca/api',
                'alphpaca/resource',
            ],
            $resolvedForLowerVersion,
        );
    }

    #[Test]
    public function it_handles_not_found_package(): void
    {
        $this->expectException(PackageResolvingException::class);

        $httpClient = new MockHttpClient([new MockResponse('', ['http_code' => 404])]);

        $resolver = new PackageReplacementsResolver($httpClient);
        $resolver->resolveFor('alphpaca', 'stack', new Constraint('>=', '2.0.0'));
    }

    #[Test]
    public function it_handles_when_no_version_matches_the_constraint(): void
    {
        $this->expectException(PackageResolvingException::class);

        $httpClient = new MockHttpClient([new MockResponse('{}')]);

        $resolver = new PackageReplacementsResolver($httpClient);
        $resolver->resolveFor('alphpaca', 'stack', new Constraint('>=', '2.0.0'));
    }
}
