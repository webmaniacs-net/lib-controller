<?php
namespace wmlib\controller;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wmlib\controller\Filter\Chain;

/**
 * Abstract route superclass
 *
 */
abstract class Route implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Router filterchain
     *
     * @var Chain
     */
    protected $filterChain;

    /**
     * Params default
     *
     * @var string[]
     */
    protected $default = [], $properties = array();

    protected function __construct()
    {

    }

    /**
     * @return \Psr\Log\LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->logger;
    }


    /**
     * Add route filter
     *
     * @param Filter $filter
     * @param array $arguments
     * @return Route
     */
    public function addFilter(Filter $filter, array $arguments = [])
    {
        if ($this->filterChain === null) {
            $this->filterChain = new Chain($this);
            if ($this->logger) {
                $this->filterChain->setLogger($this->logger);
            }
        }

        $this->filterChain->addFilter($filter, $arguments);

        return $this;
    }


    /**
     * @return string[]
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param string[] $default
     * @return self
     */
    public function setDefault(array $default)
    {
        $this->default = $default;
        return $this;
    }

    public function addDefault($name, $value)
    {
        $this->default[$name] = $value;

        return $this;
    }

    public function addProperty($name, $value)
    {
        $this->properties[$name] = $value;

        return $this;
    }


    abstract protected function dispatchRoute(Request $request, Response $response, array $arguments = []);

    /**
     * Dispatch request to application
     *
     * @param Request $request
     * @param Response $response
     * @param array $arguments
     * @return Response
     */
    final public function dispatch(Request $request, Response $response, array $arguments = [])
    {
        $this->log($request->getUrlPath());
        $return = null;

        if ($this->filterChain !== null) {
            $this->filterChain->resetPosition();

            $pre_response = $this->filterChain->resetPosition()->doPreFilter($request, $response);

            if ($pre_response instanceof Response) {
                $response = $pre_response;
            } else {
                foreach ($this->filterChain->getArguments() as $name => $value) {
                    $request = $request->withAttribute($name, $value);
                }

                $dispatch_response = $this->dispatchRoute($request, $response, $arguments);
                if ($dispatch_response instanceof Response) {
                    $response = $dispatch_response;
                }

                $post_response = $this->filterChain->doPostFilter($request, $response);
                if ($post_response instanceof Response) {
                    $response = $post_response;
                }
            }

        } else {
            $dispatch_response = $this->dispatchRoute($request, $response, $arguments);
            if ($dispatch_response instanceof Response) {
                $response = $dispatch_response;
            }
        }


        return $response;
    }

    public function __toString()
    {
        return get_class($this);
    }


    /**
     * Logs the method call or the executed SQL statement.
     *
     * @param string $msg Message to log.
     */
    protected function log($msg)
    {
        if ($msg && $this->logger) {
            $backtrace = debug_backtrace();


            $i = 1;
            $stackSize = count($backtrace);
            do {
                $callingMethod = $backtrace[$i]['function'];
                $i++;
            } while ($callingMethod == "log" && $i < $stackSize);

            $this->logger->info('[' . $callingMethod . '] ' . $msg);
        }
    }
}

