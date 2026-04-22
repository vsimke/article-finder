<?php

declare(strict_types=1);

namespace Vsimke\ArticleFinder\Finders;

abstract class Finder
{
    private ?Finder $next = null;

    /**
     * Append a finder to the end of the chain and return the appended finder.
     */
    public function chain(Finder $next): Finder
    {
        $this->next = $next;

        return $next;
    }

    /**
     * @param array<string, string> $parameters
     * @return array<string, string>
     */
    public function find(array $parameters): array
    {
        if (!$this->next instanceof \Vsimke\ArticleFinder\Finders\Finder) {
            return [];
        }

        return $this->next->find($parameters);
    }
}
