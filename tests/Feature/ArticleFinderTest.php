<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Vsimke\ArticleFinder\ArticleFinder;
use Vsimke\ArticleFinder\Scraper\HQueryScraper;

function makeClient(MockHandler $mock): Client
{
    return new Client(['handler' => HandlerStack::create($mock)]);
}

it('finds an article via Bing on the first attempt', function (): void {
    $html = file_get_contents(__DIR__ . '/../Fixtures/bing_results.html');
    assert(is_string($html));

    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], $html),
    ]);

    $scraper = new HQueryScraper(makeClient($mock));
    $finder = ArticleFinder::create($scraper);

    $result = $finder->find(['site' => 'example.com', 'title' => 'PHP Programming Guide']);

    expect($result)->not->toBeFalse()
        ->and($result['link'])->toBe('https://example.com/php-programming-guide')
        ->and($result['finder'])->toBe('www.bing.com');
});

it('falls back to Google when Bing returns no matching result', function (): void {
    $bingHtml = '<html><body><h2><a href="https://example.com">Completely Irrelevant Page</a></h2></body></html>';
    $googleHtml = file_get_contents(__DIR__ . '/../Fixtures/google_results.html');
    assert(is_string($googleHtml));

    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], $bingHtml),
        new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], $googleHtml),
    ]);

    $scraper = new HQueryScraper(makeClient($mock));
    $finder = ArticleFinder::create($scraper);

    $result = $finder->find(['site' => 'example.com', 'title' => 'PHP Programming Guide']);

    expect($result)->not->toBeFalse()
        ->and($result['link'])->toBe('https://example.com/php-programming-guide')
        ->and($result['finder'])->toBe('www.google.com');
});

it('returns false when neither Bing nor Google finds a matching result', function (): void {
    $emptyHtml = '<html><body><p>No results</p></body></html>';

    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], $emptyHtml),
        new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], $emptyHtml),
    ]);

    $scraper = new HQueryScraper(makeClient($mock));
    $finder = ArticleFinder::create($scraper);

    expect($finder->find(['site' => 'example.com', 'title' => 'PHP Programming Guide']))->toBeFalse();
});

it('handles scraper transport failures gracefully and returns false', function (): void {
    $dummyRequest = new Request('GET', 'https://www.bing.com');

    $mock = new MockHandler([
        new ConnectException('Connection refused', $dummyRequest),
        new ConnectException('Connection refused', $dummyRequest),
    ]);

    $scraper = new HQueryScraper(makeClient($mock));
    $finder = ArticleFinder::create($scraper);

    expect($finder->find(['site' => 'example.com', 'title' => 'PHP Programming Guide']))->toBeFalse();
});
