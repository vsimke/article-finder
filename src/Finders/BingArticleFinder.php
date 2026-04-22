<?php

declare(strict_types=1);

namespace Vsimke\ArticleFinder\Finders;

use Vsimke\ArticleFinder\Scraper\HQueryScraper;
use Vsimke\ArticleFinder\Scraper\Parsers\BingArticleParser;

class BingArticleFinder extends Finder
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
            "/search?go=Search&q=site:{$site} {$title}&qs=ds&form=QBRE",
            BingArticleParser::class,
            ['title' => $title],
        );

        if ($article['link'] ?? null) {
            $article['finder'] = parse_url(BingArticleParser::BASE_URI, PHP_URL_HOST);

            return $article;
        }

        return parent::find($parameters);
    }
}
