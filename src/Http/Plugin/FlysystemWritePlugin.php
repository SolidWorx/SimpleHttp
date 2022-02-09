<?php
declare(strict_types=1);

namespace SolidWorx\SimpleHttp\Http\Plugin;

use Http\Client\Common\Plugin;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Promise\Promise;
use League\Flysystem\FilesystemWriter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function is_resource;

final class FlysystemWritePlugin implements Plugin
{
    private FilesystemWriter $writer;
    private string $path;

    public function __construct(FilesystemWriter $writer, string $path)
    {
        $this->writer = $writer;
        $this->path = $path;
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $responseFactory = Psr17FactoryDiscovery::findResponseFactory();
        $streamFactory = Psr17FactoryDiscovery::findStreamFactory();

        return $next($request)->then(function (ResponseInterface $response) use ($responseFactory, $streamFactory) {
            $body = $response->getBody();
            $isSeekable = $body->isSeekable();

            $stream = $body->detach();

            if (!is_resource($stream)) {
                return $response;
            }

            $this->writer->writeStream($this->path, $stream);

            if ($isSeekable) {
                rewind($stream);
            }

            $nextResponse = $responseFactory->createResponse(
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            );

            foreach ($response->getHeaders() as $name => $values) {
                $nextResponse = $nextResponse->withHeader($name, $values);
            }

            return $nextResponse
                ->withBody($streamFactory->createStreamFromResource($stream))
                ->withProtocolVersion($response->getProtocolVersion());
        });
    }
}
