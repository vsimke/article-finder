<?php

declare(strict_types=1);

namespace Vsimke\ArticleFinder\Scraper\Parsers;

use duzun\hQuery;
use GuzzleHttp\Psr7\Header;

class GoogleArticleParser extends AbstractParser
{
    public const BASE_URI = 'https://www.google.com';

    /**
     * @return array<string, string>
     */
    public function parse(hQuery $html): array
    {
        $contentTypeValues = array_key_exists('content-type', $this->responseHeaders) ? $this->responseHeaders['content-type'] : [];
        $contentTypeHeader = Header::parse($contentTypeValues);
        $charset = ($contentTypeHeader[0] ?? [])['charset'] ?? 'utf-8';

        foreach ($html->find('a[href]') ?? [] as $linkElement) {
            foreach ($linkElement->find('h3') ?? [] as $element) {
                $text = mb_convert_encoding(str_replace('...', '', (string) $element->text()), 'utf-8', $charset);

                similar_text((string) ($this->parameters['title'] ?? ''), trim($text), $percent);

                $decoded = html_entity_decode((string) $linkElement->attr('href'));

                if ($percent > 70 && filter_var($decoded, FILTER_VALIDATE_URL)) {
                    return [
                        'title' => (string) ($this->parameters['title'] ?? ''),
                        'link' => $decoded,
                    ];
                }
            }
        }

        return [];
    }

    public function generateUrl(string $url): string
    {
        return self::BASE_URI . $url;
    }
}
