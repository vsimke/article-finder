<?php

declare(strict_types=1);

namespace Vsimke\ArticleFinder\Scraper;

use duzun\hQuery;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Vsimke\ArticleFinder\Scraper\Parsers\AbstractParser;

class HQueryScraper
{
    private ClientInterface $client;

    /** @var array<string, string> */
    private array $requestHeaders;

    /**
     * @param array{headers?: array<string, string>, verify?: bool} $config
     */
    public function __construct(?ClientInterface $client = null, array $config = [])
    {
        $this->requestHeaders = array_merge([
            'Accept' => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36',
        ], $config['headers'] ?? []);

        $this->client = $client ?? new Client([
            'verify' => $config['verify'] ?? true,
        ]);
    }

    /**
     * Fetch the URL, parse with the given parser class, and return the result.
     *
     * @param class-string<AbstractParser> $parserClass
     * @param array<string, mixed> $params
     * @return array<string, string>
     */
    public function get(string $path, string $parserClass, array $params = []): array
    {
        $parser = new $parserClass($params);
        $url = $parser->generateUrl($path);

        try {
            $response = $this->client->send(new Request('GET', $url, $this->requestHeaders));
        } catch (GuzzleException) {
            return [];
        }

        $html = hQuery::fromHtml($response->getBody()->getContents());
        $parser->setResponseHeaders(array_change_key_case($response->getHeaders(), CASE_LOWER));

        return $parser->parse($html);
    }

    /**
     * Override request headers (e.g. for custom User-Agent).
     *
     * @param array<string, string> $headers
     */
    public function setRequestHeaders(array $headers): self
    {
        $this->requestHeaders = $headers;

        return $this;
    }
}
