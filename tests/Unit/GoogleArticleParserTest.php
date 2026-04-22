<?php

declare(strict_types=1);

use duzun\hQuery;
use Vsimke\ArticleFinder\Scraper\Parsers\GoogleArticleParser;

it('parses a matching article link from Google SERP HTML', function (): void {
    $fixtureHtml = file_get_contents(__DIR__ . '/../Fixtures/google_results.html');
    assert(is_string($fixtureHtml));

    $html = hQuery::fromHtml($fixtureHtml);
    $parser = new GoogleArticleParser(['title' => 'PHP Programming Guide']);
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
        '<html><body><a href="https://example.com"><h3>Totally Different Content</h3></a></body></html>',
    );

    $parser = new GoogleArticleParser(['title' => 'PHP Programming Guide']);
    $parser->setResponseHeaders(['content-type' => ['text/html; charset=utf-8']]);

    expect($parser->parse($html))->toBeEmpty();
});

it('returns empty array when there are no a>h3 elements', function (): void {
    $html = hQuery::fromHtml('<html><body><p>No links or headings here</p></body></html>');

    $parser = new GoogleArticleParser(['title' => 'PHP Programming Guide']);
    $parser->setResponseHeaders(['content-type' => ['text/html; charset=utf-8']]);

    expect($parser->parse($html))->toBeEmpty();
});

it('defaults to utf-8 charset when content-type header is absent', function (): void {
    $fixtureHtml = file_get_contents(__DIR__ . '/../Fixtures/google_results.html');
    assert(is_string($fixtureHtml));

    $html = hQuery::fromHtml($fixtureHtml);
    $parser = new GoogleArticleParser(['title' => 'PHP Programming Guide']);
    // No response headers set — defaults to utf-8

    $result = $parser->parse($html);

    expect($result)->toHaveKey('link');
});

it('prepends BASE_URI when generating a URL', function (): void {
    $parser = new GoogleArticleParser();

    expect($parser->generateUrl('/search?q=test'))->toBe('https://www.google.com/search?q=test');
});
