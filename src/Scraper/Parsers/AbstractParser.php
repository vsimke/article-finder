<?php

declare(strict_types=1);

namespace Vsimke\ArticleFinder\Scraper\Parsers;

use duzun\hQuery;

abstract class AbstractParser
{
    /** @var array<string, mixed>|null */
    protected ?array $parameters;

    /** @var array<string, string[]> */
    protected array $responseHeaders = [];

    /**
     * @param array<string, mixed>|null $parameters
     */
    public function __construct(?array $parameters = null)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array<string, string>
     */
    abstract public function parse(hQuery $html): array;

    /**
     * @param array<string, string[]> $headers
     */
    public function setResponseHeaders(array $headers): void
    {
        $this->responseHeaders = $headers;
    }

    public function generateUrl(string $url): string
    {
        return $url;
    }
}
