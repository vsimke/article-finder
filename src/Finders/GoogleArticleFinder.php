<?php

declare(strict_types=1);

namespace Vsimke\ArticleFinder\Finders;

use Vsimke\ArticleFinder\Scraper\HQueryScraper;
use Vsimke\ArticleFinder\Scraper\Parsers\GoogleArticleParser;

class GoogleArticleFinder extends Finder
{
    public function __construct(protected HQueryScraper $scraper)
    {
    }

    /**
     * @param array<string, string> $parameters
     * @return array<string, string>
     */
    public function find(array $parameters): array
    {
        $site = $parameters['site'] ?? '';
        $title = $parameters['title'] ?? '';

        $article = $this->scraper->get(
            "/search?q=site:{$site} {$title}",
            GoogleArticleParser::class,
            ['title' => $title],
        );

        if ($article['link'] ?? null) {
            $article['finder'] = parse_url(GoogleArticleParser::BASE_URI, PHP_URL_HOST);

            return $article;
        }

        return parent::find($parameters);
    }
}
