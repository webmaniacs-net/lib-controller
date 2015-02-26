<?php
namespace wmlib\controller\Route;

use wmlib\controller\Exception\PropertyNotFoundException;
use wmlib\controller\IResponseDecorator;
use wmlib\controller\Request;
use wmlib\controller\Response;
use wmlib\controller\Route;
use wmlib\controller\Router;

/**
 * MVC controller route
 *
 */
class Controller extends Route
{
    const HANDLER_SKIP_RENDER = 2;
    const POST_REDIRECT = 1;

    /**
     * @var IResponseDecorator
     */
    protected $_responseDecorator;

    /**
     * Controller action
     *
     * @var string
     */
    private $action;

    /**
     * Reflection method
     *
     * @var \ReflectionMethod
     */
    private $reflectionMethod;

    /**
     * @var object
     */
    protected $controller;

    public function __construct($controller = null, $action = null)
    {
        parent::__construct('');

        if ($action === null && is_array($controller)) {
            list($controller, $action) = $controller;
        }

        $this->action = $action;
        $this->controller = $controller ? $controller : $this;
    }

    public function setResponseDecorator(IResponseDecorator $decorator)
    {
        $this->_responseDecorator = $decorator;
    }

    /**
     * Initilize method reflection
     *
     * @throws \Exception something wrong
     * @return \ReflectionMethod
     */
    protected function initialize()
    {
        if ($this->reflectionMethod === null) {
            $reflection = new \ReflectionObject($this->controller);


            $method_name = 'handle' . ucfirst($this->action);
            if ($reflection->hasMethod($method_name)) {

                $this->reflectionMethod = $reflection->getMethod($method_name);

                foreach ($this->reflectionMethod->getParameters() as $param) {
                    /* @var $param \ReflectionParameter */

                    if ($param->isOptional() && ($default = $param->getDefaultValue()) !== null) {
                        $this->default[$param->getName()] = $default;
                    }
                }

            } else {
                throw new \Exception("Action method $method_name not found");
            }

        }

        return $this->reflectionMethod;
    }

    public function __destruct()
    {
        unset($this->reflectionMethod);
    }


    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return object|null
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param object $controller
     */
    public function setController($controller)
    {

        $this->controller = $controller;

        $this->reflectionMethod = null;
    }

    protected function dispatchRoute(Request $request, Response $response, array $arguments = [])
    {
        $this->initialize();

        if ($request->isPost()) {
            // tries to found POST handler
            $method_name = 'post' . $this->action;
            $reflection = new \ReflectionObject($this->controller);
            if ($reflection->hasMethod($method_name)) {
                $post_return = $this->_dispatchMethod($reflection->getMethod($method_name), $request, $response,
                    $arguments);
                if ($post_return === self::POST_REDIRECT) {

                    return Response\Factory::Redirect($response, $request->getUrl(true));
                }
            }
        }


        $return = $this->_dispatchMethod($this->reflectionMethod, $request, $response, $arguments);


        if ($return instanceof Response) {
            $response = $return;
            $return = [];
        }


        $response = $this->_decorateResponse($response,
            array_merge($request->getAttributes(), $arguments, $return ? (array)$return : []));

        return $response;
    }

    protected function _decorateResponse(Response $response, $params)
    {
        if ($this->_responseDecorator) {
            return $this->_responseDecorator->decorateResponse($response, $params);
        } else {
            return $response;
        }
    }

    /**
     * @param string $param_name
     * @param Request $request
     * @param array $arguments
     * @return mixed
     * @throws PropertyNotFoundException
     */
    protected function _prepareMethodParam(
        $param_name,
        Request $request,
        array $arguments = []
    )
    {

        if (isset($arguments[$param_name])) {
            return $arguments[$param_name];
        } elseif ($request->hasAttribute($param_name)) {
            return $request->getAttribute($param_name);
        } else {
            throw new PropertyNotFoundException("Controller action argument $param_name not found");
        }
    }

    protected function _dispatchMethod(
        \ReflectionMethod $method,
        Request $request,
        Response $response,
        array $arguments = []
    )
    {
        /**
         * Create view
         */
        $signature = array();

        foreach ($method->getParameters() as $param) {
            $param_name = $param->getName();

            /* @var $param \ReflectionParameter */
            if ($param_class = $param->getClass()) {
                switch ($param_class->getName()) {
                    case self::CLASS:
                        $signature[$param_name] = $this;
                        continue 2;
                    case Response::CLASS:
                        $signature[$param_name] = $response;
                        continue 2;
                    case Request::CLASS:
                        $signature[$param_name] = $request;
                        continue 2;
                }
            }

            $value = null;
            try {
                $value = $this->_prepareMethodParam($param_name, $request, $arguments);
            } catch (PropertyNotFoundException $e) {
                if ($param->isOptional()) $value = $param->getDefaultValue();
                else throw $e;
            }

            if ($param->isOptional() && ($default = $param->getDefaultValue()) !== null) {
                if (is_int($default)) {
                    $value = (int)($value);
                } elseif (is_bool($default)) {
                    $value = (bool)($value);
                }
            }

            $signature[$param_name] = $value;
        }

        return $method->invokeArgs($this->controller, $signature);
    }
}
