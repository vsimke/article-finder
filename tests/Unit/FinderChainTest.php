<?php

declare(strict_types=1);

use Vsimke\ArticleFinder\ArticleFinder;
use Vsimke\ArticleFinder\Finders\Finder;

it('returns article when first finder finds a result and short-circuits', function (): void {
    $foundFinder = new class () extends Finder {
        public function find(array $parameters): array
        {
            return ['title' => 'Test Article', 'link' => 'https://example.com/test', 'finder' => 'test'];
        }
    };

    $shouldNotRun = new class () extends Finder {
        public function find(array $parameters): array
        {
            return ['title' => 'WRONG', 'link' => 'https://wrong.com', 'finder' => 'wrong'];
        }
    };

    $foundFinder->chain($shouldNotRun);
    $articleFinder = new ArticleFinder($foundFinder);

    $result = $articleFinder->find(['site' => 'example.com', 'title' => 'Test Article']);

    expect($result)->not->toBeFalse()
        ->and($result['link'])->toBe('https://example.com/test')
        ->and($result['finder'])->toBe('test');
});

it('falls through to the next finder when the first returns no link', function (): void {
    $notFoundFinder = new class () extends Finder {
        public function find(array $parameters): array
        {
            return parent::find($parameters);
        }
    };

    $fallbackFinder = new class () extends Finder {
        public function find(array $parameters): array
        {
            return ['title' => 'Fallback Article', 'link' => 'https://fallback.com/article', 'finder' => 'fallback'];
        }
    };

    $notFoundFinder->chain($fallbackFinder);
    $articleFinder = new ArticleFinder($notFoundFinder);

    $result = $articleFinder->find(['site' => 'example.com', 'title' => 'Fallback Article']);

    expect($result)->not->toBeFalse()
        ->and($result['link'])->toBe('https://fallback.com/article');
});

it('returns false when all finders return empty', function (): void {
    $notFound = new class () extends Finder {
        public function find(array $parameters): array
        {
            return parent::find($parameters);
        }
    };

    $alsoNotFound = new class () extends Finder {
        public function find(array $parameters): array
        {
            return parent::find($parameters);
        }
    };

    $notFound->chain($alsoNotFound);
    $articleFinder = new ArticleFinder($notFound);

    expect($articleFinder->find(['site' => 'example.com', 'title' => 'Missing Article']))->toBeFalse();
});

it('returns false when the chain has no finders with results', function (): void {
    $empty = new class () extends Finder {
        public function find(array $parameters): array
        {
            return parent::find($parameters);
        }
    };

    $articleFinder = new ArticleFinder($empty);

    expect($articleFinder->find(['site' => 'example.com', 'title' => 'No Match']))->toBeFalse();
});

it('can swap the finder via setFinder', function (): void {
    $initialFinder = new class () extends Finder {
        public function find(array $parameters): array
        {
            return [];
        }
    };

    $replacementFinder = new class () extends Finder {
        public function find(array $parameters): array
        {
            return ['title' => 'Swapped', 'link' => 'https://swapped.com', 'finder' => 'swapped'];
        }
    };

    $articleFinder = new ArticleFinder($initialFinder);
    $articleFinder->setFinder($replacementFinder);

    $result = $articleFinder->find(['site' => 'example.com', 'title' => 'Swapped']);

    expect($result)->not->toBeFalse()
        ->and($result['link'])->toBe('https://swapped.com');
});
