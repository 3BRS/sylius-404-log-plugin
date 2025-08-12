<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Fixture;

use Doctrine\Persistence\ObjectManager;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Bundle\FixturesBundle\Fixture\FixtureInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use ThreeBRS\Sylius404LogPlugin\Entity\NotFoundLog;

class NotFoundLogFixture extends AbstractFixture implements FixtureInterface
{
    private ObjectManager $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function getName(): string
    {
        return 'three_brs_404_not_found_log';
    }

    /**
     * @param array{amount:int,domains:array<int,string>,user_agents?:array<int,string>} $options
     */
    public function load(array $options): void
    {
        $amount = $options['amount'];
        $domains = $options['domains'];
        $userAgents = (array) ($options['user_agents'] ?? [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) Gecko/20100101 Firefox/124.0',
            'Googlebot/2.1 (+http://www.google.com/bot.html)',
            'curl/8.0.1',
        ]);

        // Prepare popular domain+slug pairs to ensure aggregation has duplicates
        $popularPairs = [];
        $popularCount = min(50, max(10, (int) ($amount / 20)));
        $usedSlugs = [];
        for ($p = 0; $p < $popularCount; ++$p) {
            $domain = $domains[array_rand($domains)];
            // ensure unique slug in pool to represent distinct groups
            do {
                $slug = $this->generateRandomSlug();
            } while (isset($usedSlugs[$slug]));
            $usedSlugs[$slug] = true;
            $popularPairs[] = ['domain' => $domain, 'slug' => $slug];
        }

        for ($i = 0; $i < $amount; ++$i) {
            $log = new NotFoundLog();

            if (random_int(1, 100) <= 70) {
                $pair = $popularPairs[array_rand($popularPairs)];
                /** @var array{domain:string,slug:string} $pair */
                $domain = $pair['domain'];
                $slug = $pair['slug'];
            } else {
                $domain = $domains[array_rand($domains)];
                $slug = $this->generateRandomSlug();
            }

            $queryString = $this->maybeGenerateQueryString();
            $userAgent = $this->maybePickUserAgent($userAgents);
            $createdAt = $this->randomDateTimeImmutable('-31 days', 'now');

            $log->setUrlDomain($domain);
            $log->setUrlSlug($slug);
            $log->setQueryString($queryString);
            $log->setUserAgent($userAgent);
            $log->setCreatedAt($createdAt);

            $this->manager->persist($log);
        }

        $this->manager->flush();
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
                ->integerNode('amount')
                    ->min(1)
                    ->defaultValue(500)
                ->end()
                ->arrayNode('domains')
                    ->performNoDeepMerging()
                    ->prototype('scalar')->end()
                    ->defaultValue(['example.com', 'shop.local', 'test.local'])
                ->end()
                ->arrayNode('user_agents')
                    ->performNoDeepMerging()
                    ->prototype('scalar')->end()
                    ->defaultValue([
                        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
                        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
                        'Mozilla/5.0 (X11; Linux x86_64) Gecko/20100101 Firefox/124.0',
                        'Googlebot/2.1 (+http://www.google.com/bot.html)',
                        'curl/8.0.1',
                    ])
                ->end()
            ->end()
        ;
    }

    private function generateRandomSlug(): string
    {
        $segments = random_int(1, 4);
        $parts = [];
        for ($i = 0; $i < $segments; ++$i) {
            $parts[] = $this->randomSlugPart();
        }

        return '/' . implode('/', $parts);
    }

    private function randomSlugPart(): string
    {
        $words = [
            'product', 'category', 'search', 'blog', 'news', 'sale', 'clearance', 'summer', 'winter', 'collection',
            'mens', 'womens', 'kids', 'accessories', 'shoes', 'electronics', 'books', 'toys', 'home', 'garden',
        ];

        $word = $words[array_rand($words)];
        $suffix = (string) random_int(1, 9999);

        return $word . '-' . $suffix;
    }

    private function maybeGenerateQueryString(): ?string
    {
        if (random_int(0, 1) === 0) {
            return null;
        }

        $params = [
            'utm_source' => ['google', 'newsletter', 'facebook', 'direct'],
            'utm_campaign' => ['spring', 'summer', 'autumn', 'winter'],
            'ref' => ['ext', 'partner', 'email'],
            'q' => ['test', 'abc', 'shoes', 'sale'],
        ];

        $pairs = [];
        $count = random_int(1, 3);
        $keys = array_rand($params, $count);
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        foreach ($keys as $k) {
            $values = $params[$k];
            $pairs[] = urlencode((string) $k) . '=' . urlencode((string) $values[array_rand($values)]);
        }

        return implode('&', $pairs);
    }

    /**
     * @param array<int,string> $userAgents
     */
    private function maybePickUserAgent(array $userAgents): ?string
    {
        if (random_int(0, 4) === 0) {
            return null; // some requests may have no/empty UA
        }

        return $userAgents[array_rand($userAgents)] ?? null;
    }

    private function randomDateTimeImmutable(string $start, string $end): \DateTimeImmutable
    {
        $startTs = (new \DateTimeImmutable($start))->getTimestamp();
        $endTs = (new \DateTimeImmutable($end))->getTimestamp();
        $ts = random_int(min($startTs, $endTs), max($startTs, $endTs));

        return (new \DateTimeImmutable())->setTimestamp($ts);
    }
}
