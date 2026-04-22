<?php

declare(strict_types=1);

use duzun\hQuery;
use Vsimke\ArticleFinder\Scraper\Parsers\BingArticleParser;

it('parses a matching article link from Bing SERP HTML', function (): void {
    $fixtureHtml = file_get_contents(__DIR__ . '/../Fixtures/bing_results.html');
    assert(is_string($fixtureHtml));

    $html = hQuery::fromHtml($fixtureHtml);
    $parser = new BingArticleParser(['title' => 'PHP Programming Guide']);
    $parser->setResponseHeaders(['content-type' => ['text/html; charset=utf-8']]);

    $result = $parser->parse($html);

    expect($result)
        ->toHaveKey('title')
        ->toHaveKey('link')
        ->and($result['link'])->toBe('https://example.com/php-programming-guide')
        ->and($result['title'])->toBe('PHP Programming Guide');
});

it('returns empty array when no link exceeds similarity threshold', function (): void {
    $html = hQuery::fromHtml(
        '<html><body><h2><a href="https://example.com">Completely Unrelated Content Here</a></h2></body></html>',
    );

    $parser = new BingArticleParser(['title' => 'PHP Programming Guide']);
    $parser->setResponseHeaders(['content-type' => ['text/html; charset=utf-8']]);

    expect($parser->parse($html))->toBeEmpty();
});

it('returns empty array when there are no h2>a elements', function (): void {
    $html = hQuery::fromHtml('<html><body><p>No headings here</p></body></html>');

    $parser = new BingArticleParser(['title' => 'PHP Programming Guide']);
    $parser->setResponseHeaders(['content-type' => ['text/html; charset=utf-8']]);

    expect($parser->parse($html))->toBeEmpty();
});

it('defaults to utf-8 charset when content-type header is absent', function (): void {
    $fixtureHtml = file_get_contents(__DIR__ . '/../Fixtures/bing_results.html');
    assert(is_string($fixtureHtml));

    $html = hQuery::fromHtml($fixtureHtml);
    $parser = new BingArticleParser(['title' => 'PHP Programming Guide']);
    // No response headers set — defaults to utf-8

    $result = $parser->parse($html);

    expect($result)->toHaveKey('link');
});

it('prepends BASE_URI when generating a URL', function (): void {
    $parser = new BingArticleParser();

    expect($parser->generateUrl('/search?q=test'))->toBe('https://www.bing.com/search?q=test');
});
