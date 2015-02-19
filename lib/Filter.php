<?php
namespace wmlib\controller;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wmlib\controller\Filter\Chain;

/**
 * Abstract filter support superclass
 *
 *
 */
abstract class Filter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
        return $filterChain->doPreFilter($request, $response);
    }

    /**
     * Post filter method.
     * This should be redefined in post filter.
     *
     * For process the rest of filter chain this code should be call inside:
     * <code>
     * $filterChain->doPostFilter ();
     * </code>
     *
     * @param Request $request
     * @param Response $response
     * @param Chain $filterChain
     * @param bool $flag
     * @return Response|void
     */
    public function doPostFilter(Request $request, Response $response, Chain $filterChain, $flag = true)
    {
        return $filterChain->doPostFilter($request, $response, $flag);
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
