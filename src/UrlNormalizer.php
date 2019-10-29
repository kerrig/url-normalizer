<?php

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

namespace Testing;

class UrlNormalizer implements MiddlewareInterface
{
    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $this->normalize($uri->getPath());

        if ($uri->getPath() !== $path) {
            return $this->createResponse(301)
                ->withHeader('Location', (string) $uri->withPath($path));
        }

        return $handler->handle($request->withUri($uri->withPath($path)));
    }

    /**
     * 
     */
    public function normalize(string $path): string
    {
        if ($path === '') {
            return '/';
        }

        $path = $this->removeTrailingSlash($path);
        $path = $this->handleDotSegments($path);
        $path = $this->lowerUrl($path);

        return $path;
    }

    /**
     * 
     */
    private function lowerUrl(string $path): string
    {
        return strtolower($path);
    }

    /**
     * 
     */
    private function removeTrailingSlash(string $path): string
    {
        if (strlen($path) > 1) {
            return rtrim($path, '/');
        }

        return $path;
    }

    /**
     * 
     */
    private function handleDotSegments(string $path): string
    {
    	$parts = [];

		foreach (explode('/', $path) as $part) {
	        if ($part == '.') {
                continue;
	        }

	        if ($part == '..') {
                if (count($parts)) {
                    array_pop($parts);
                }

                continue;
	        }

	        if ($part != '') {
                $parts[] = $part;
	        }
		}

		return '/' . implode('/', $parts);
    }
}