# Article Finder

A framework-agnostic PHP package for finding published articles via Bing and Google SERP scraping, using a chain-of-responsibility finder pattern.

<p align="center">
<a href="https://github.com/vsimke/article-finder/actions"><img src="https://github.com/vsimke/article-finder/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/vsimke/article-finder"><img src="https://img.shields.io/packagist/dt/vsimke/article-finder" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/vsimke/article-finder"><img src="https://img.shields.io/packagist/v/vsimke/article-finder" alt="Latest Stable Version"></a>
<a href="https://github.com/vsimke/article-finder/releases"><img src="https://img.shields.io/github/v/tag/vsimke/article-finder" alt="GitHub Tag"></a>
<a href="https://packagist.org/packages/vsimke/article-finder"><img src="https://img.shields.io/packagist/l/vsimke/article-finder" alt="License"></a>
</p>

Given a site domain and article title, the package queries Bing first and falls back to Google if no sufficiently similar result is found. You can swap or extend the chain with your own finders.

## Requirements

- PHP 8.1+
- Guzzle 7+

## Installation

```bash
composer require vsimke/article-finder
```

## Usage

### Default chain (Bing → Google)

```php
use Vsimke\ArticleFinder\ArticleFinder;
use Vsimke\ArticleFinder\FinderParameter;
use Vsimke\ArticleFinder\Scraper\HQueryScraper;

$scraper = new HQueryScraper();
$finder  = ArticleFinder::create($scraper);

$result = $finder->find([
    FinderParameter::SITE  => 'example.com',
    FinderParameter::TITLE => 'My Article Title',
]);

if ($result !== false) {
    echo $result['title'];  // 'My Article Title'
    echo $result['link'];   // 'https://example.com/my-article-title'
    echo $result['finder']; // 'www.bing.com' or 'www.google.com'
}
```

`ArticleFinder::find()` returns `array<string, string>` on success or `false` when no match is found across the whole chain.

### Dependency injection

```php
use Vsimke\ArticleFinder\ArticleFinder;
use Vsimke\ArticleFinder\FinderParameter;
use Vsimke\ArticleFinder\Scraper\HQueryScraper;

class ArticleOnlineChecker
{
    public function __construct(private readonly ArticleFinder $finder) {}

    public function check(string $site, string $title): array|false
    {
        return $this->finder->find([
            FinderParameter::SITE  => $site,
            FinderParameter::TITLE => $title,
        ]);
    }
}

// Wire it up
$checker = new ArticleOnlineChecker(
    ArticleFinder::create(new HQueryScraper())
);
```

### Scraper options

Pass a custom `ClientInterface` or override the config array to control transport:

```php
use GuzzleHttp\Client;
use Vsimke\ArticleFinder\Scraper\HQueryScraper;

// Custom Guzzle client (e.g. with a proxy)
$client  = new Client(['proxy' => 'socks5://127.0.0.1:1080']);
$scraper = new HQueryScraper($client);

// Override User-Agent
$scraper = new HQueryScraper(config: [
    'headers' => [
        'User-Agent' => 'MyBot/1.0',
    ],
]);
```

### Custom finder chain

Build your own chain by extending `Finder` and wiring it with `setFinder()`:

```php
use Vsimke\ArticleFinder\Finders\Finder;

class DuckDuckGoFinder extends Finder
{
    public function find(array $parameters): array
    {
        // ... scrape DuckDuckGo ...

        if ($article['link'] ?? null) {
            return $article;
        }

        return parent::find($parameters); // delegate to next in chain
    }
}

$ddg    = new DuckDuckGoFinder();
$google = new GoogleArticleFinder($scraper);
$ddg->chain($google);

$finder->setFinder($ddg);
```

## How it works

```
ArticleFinder::find()
  └─ BingArticleFinder::find()        ← queries www.bing.com
       ├─ match found → return result
       └─ no match    → GoogleArticleFinder::find()   ← queries www.google.com
                          ├─ match found → return result
                          └─ no match    → []  →  ArticleFinder returns false
```

A result is considered a match when `similar_text()` similarity between the SERP title and the search title exceeds 70%.

> **Note on fragility:** SERP markup changes regularly. The parser fixture tests in `tests/Unit/` are the safety net — update the fixtures if Bing or Google changes their HTML structure.

## Development

### Run tests

```bash
./vendor/bin/pest
```

### Static analysis

```bash
./vendor/bin/phpstan analyse
```

### Code style (PSR-12)

```bash
# Check only
./vendor/bin/pint --test

# Fix
./vendor/bin/pint
```

### Rector

```bash
# Dry run
./vendor/bin/rector process --dry-run

# Apply
./vendor/bin/rector process
```

### Release

```bash
.github/release.sh 1.0.0
```

This tags `v1.0.0` locally and pushes it to origin, triggering any tag-based CI jobs.

## License

MIT — see [LICENSE](https://opensource.org/licenses/MIT).
