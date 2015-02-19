<?php
namespace wmlib\controller\Filter;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wmlib\controller\Filter;
use wmlib\controller\Request;
use wmlib\controller\Response;
use wmlib\controller\Route;

/**
 * Filter chain support.
 * Filter chain can be applicable to project? application and route
 *
 */
class Chain implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Route
     */
    private $_route;

    private $_position = 0;

    /**
     * Filters chain
     *
     * @var array
     */
    private $_filters;

    private $_arguments = [];

    public function __construct(Route $route)
    {
        $this->_position = 0;
        $this->_route = $route;
        $this->logger = $route->getLogger();
        $this->_filters = [];
    }

    /**
     * @return Route
     */
    public function getRoute()
    {
        return $this->_route;
    }

    /**
     * Reset internal filter position
     *
     * @return Chain Fluent API support
     */
    public function resetPosition()
    {
        $this->_position = 0;

        return $this;
    }

    /**
     * Get current filter arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Add filter
     *
     * @param Filter $filter
     * @param array $arguments
     * @return Chain
     */
    public function addFilter(Filter $filter, array $arguments = [])
    {
        $this->_filters[] = [$filter, $arguments];

        if ($this->logger) {
            $filter->setLogger($this->logger);
        }

        return $this;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param bool $flag
     * @return Response|void
     */
    public function doPostFilter(Request $request, Response $response, $flag = true)
    {
        $this->_position--;

        if (isset($this->_filters[$this->_position])) {
            list($filter, $arguments) = $this->_filters[$this->_position];
            $this->_arguments = $arguments;

            $filter->doPostFilter($request, $response, $this, $flag);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response|void
     */
    public function doPreFilter(Request $request, Response $response)
    {
        if (isset($this->_filters[$this->_position])) {
            list($filter, $arguments) = $this->_filters[$this->_position++];
            $this->_arguments = $arguments;

            return $filter->doPreFilter($request, $response, $this);
        } else {
            return null;
        }

    }

    public function __toString()
    {
        $stack = array();
        foreach ($this->_filters as list($filter, $arguments)) {
            $stack[] = $filter->__toString();
        }

        return implode(' > ', $stack);
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
