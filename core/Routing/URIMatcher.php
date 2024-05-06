<?php

namespace Mini\Cms\Routing;

class URIMatcher
{
    private $patterns;

    private mixed $matched_pattern;

    public function getMatchedPattern(): mixed
    {
        return $this->matched_pattern;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    private $params;

    public function __construct(array $patterns) {
        $this->patterns = $patterns;
        $this->params = [];
    }

    public function matchCurrentURI($currentURI): bool
    {
        foreach ($this->patterns as $pattern) {
            if ($this->matchPattern($currentURI, $pattern)) {
                $this->matched_pattern = $pattern;
                return true;
            }
        }
        return false;
    }

    private function matchPattern($currentURI, $pattern): bool
    {
        $regex = $this->patternToRegex($pattern);
        $matches = [];

        if (preg_match($regex, $currentURI, $matches)) {
            // Remove the first element which is the full match
            array_shift($matches);

            // Extract placeholder values
            $params = [];
            preg_match_all('/{(\w+)}/', $pattern, $paramNames);
            foreach ($paramNames[1] as $index => $paramName) {
                $params[$paramName] = $matches[$index];
            }
            if($params) {
                $this->params = $params;
            }
            return true;
        }

        return false;
    }

    private function patternToRegex($pattern): string
    {
        // Escape special characters in the pattern
        $regex = preg_quote($pattern, '/');

        // Replace {placeholders} with regex capture groups
        $regex = preg_replace('/\\\{(\w+)\\\}/', '(?P<$1>\w+)', $regex);

        // Add start and end anchors
        return '/^' . $regex . '$/';
    }
}