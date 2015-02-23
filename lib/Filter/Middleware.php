<?php
namespace wmlib\controller\Filter;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use wmlib\controller\Filter;

use wmlib\controller\Request;
use wmlib\controller\Response;
use wmlib\controller\Route;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use wmlib\controller\Stream\String;

/**
 * Middlewares support.
 * Filter can reuse any Symfony HttpKernelInterface middleware
 *
 */
class Middleware extends Filter
{
    /**
     * @var HttpKernelInterface
     */
    private $middleware;

    private $exclusive;

    /**
     * @param HttpKernelInterface $middleware
     * @param bool $exclusive True is filter should use middleware response in ex way and pass all next's filters and route
     */
    public function __construct(HttpKernelInterface $middleware, $exclusive = true)
    {
        $this->middleware = $middleware;
        $this->exclusive = $exclusive;
    }

    /**
     * Pre filter method
     *
     * For process the rest of filter chain this code should be call inside:
     * <code>
     * $filterChain->doPreFilter ();
     * </code>
     *
     * @param Request $request
     * @param Response $response
     * @param Chain $filterChain
     * @return Response|null
     */
    public function doPreFilter(Request $request, Response $response, Chain $filterChain)
    {
        $symphony_response = $this->middleware->handle($this->decorateRequest($request), HttpKernelInterface::MASTER_REQUEST, false);

        $response = $this->mergeResponse($response, $symphony_response);

        if ($this->exclusive) {
            return $response;
        }
        return $filterChain->doPreFilter($request, $response);
    }

    /**
     * Decorate lib request to symfony request
     *
     * @param Request $request
     * @return SymfonyRequest
     */
    private function decorateRequest(Request $request)
    {
        $symfony_request = new SymfonyRequest($request->getQueryParams(), $request->getBodyParams(), $request->getAttributes(), $request->getCookies());

        return $symfony_request;
    }

    /**
     * Merge symfony response to lib response
     *
     * @param Response $response
     * @param SymfonyResponse $symfonyResponse
     * @return Response
     */
    private function mergeResponse(Response $response, SymfonyResponse $symfonyResponse)
    {
        foreach ($symfonyResponse->headers as $header => $line) {
            $response = $response->withHeader($header, $line);
        }
        $response = $response->withBody(new String($symfonyResponse->getContent()));
        return $response;
    }
}