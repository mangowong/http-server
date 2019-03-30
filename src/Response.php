<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Exception\HttpException;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Hyperf\Utils\Str;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Swoft\Http\Message\Stream\SwooleStream;

class Response implements ResponseInterface
{
    /**
     * Format data to JSON and return data with Content-Type:application/json header.
     *
     * @param array|Arrayable|Jsonable $data
     */
    public function json($data): PsrResponseInterface
    {
        $data = $this->toJson($data);
        return $this->getResponse()
            ->withAddedHeader('Content-Type', 'application/json')
            ->withBody(new SwooleStream($data));
    }

    /**
     * Format data to a string and return data with Content-Type:text/plain header.
     * @param mixed $data
     */
    public function raw($data): PsrResponseInterface
    {
        return $this->getResponse()
            ->withAddedHeader('Content-Type', 'text/plain')
            ->withBody(new SwooleStream((string) $data));
    }

    public function redirect(
        string $toUrl,
        int $status = 302,
        array $headers = [],
        string $schema = 'http'
    ): PsrResponseInterface {
        $toUrl = value(function () use ($toUrl, $schema) {
            if (! ApplicationContext::hasContainter() || Str::startsWith($toUrl, 'http://', 'https://')) {
                return $toUrl;
            }
            /** @var Contract\RequestInterface $request */
            $request = ApplicationContext::getContainer()->get(Contract\RequestInterface::class);
            $uri = $request->getUri();
            $host = $uri->getAuthority();
            // Build the url by $schema and host.
            return $schema . '://' . $host . '/' . $toUrl;
        });
        $response = $this->getResponse()->withStatus($status)->withAddedHeader('Location', $toUrl);
        foreach ($headers as $key => $value) {
            $response = $response->withAddedHeader($key, $value);
        }
        return $response;
    }

    /**
     * @param array|Arrayable|Jsonable $data
     * @throws HttpException when the data encoding error
     */
    private function toJson($data): string
    {
        if (is_array($data)) {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        if ($data instanceof Jsonable) {
            return (string) $data;
        }

        if ($data instanceof Arrayable) {
            return json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);
        }

        throw new HttpException('Error encoding response data to JSON.');
    }

    /**
     * Get the response object from context.
     */
    private function getResponse(): PsrResponseInterface
    {
        return Context::get(PsrResponseInterface::class);
    }
}
