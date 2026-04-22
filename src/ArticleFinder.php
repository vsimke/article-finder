<?php

declare(strict_types=1);

namespace Vsimke\ArticleFinder;

use Vsimke\ArticleFinder\Finders\BingArticleFinder;
use Vsimke\ArticleFinder\Finders\Finder;
use Vsimke\ArticleFinder\Finders\GoogleArticleFinder;
use Vsimke\ArticleFinder\Scraper\HQueryScraper;

class ArticleFinder
{
    public function __construct(private Finder $finder)
    {
    }

    /**
     * Build an ArticleFinder with the default Bing → Google chain.
     */
    public static function create(HQueryScraper $scraper): self
    {
        $bing = new BingArticleFinder($scraper);
        $bing->chain(new GoogleArticleFinder($scraper));

        return new self($bing);
    }

    public function setFinder(Finder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }

    /**
     * Find an article matching the given parameters.
     *
     * @param array<string, string> $parameters  Keys: FinderParameter::SITE, FinderParameter::TITLE
     * @return array<string, string>|false
     */
    public function find(array $parameters): array|false
    {
        $article = $this->finder->find($parameters);

        if ($article['link'] ?? null) {
            return $article;
        }

        return false;
    }
}
