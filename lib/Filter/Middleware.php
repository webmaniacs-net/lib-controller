<?php
namespace wmlib\controller\Filter;

use wmlib\controller\Filter;

use wmlib\controller\Response;
use wmlib\controller\Route;

/**
 * Middlewares support.
 * Filter can reuse any Symfony HttpKernelInterface middleware
 *
 */
class Middleware extends Filter
{
    public function __construct(HttpKernelInterface $middleware){

    }
}