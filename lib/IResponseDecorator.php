<?php
namespace wmlib\controller;


interface IResponseDecorator
{
    /**
     * @param Response $response
     * @param array $arguments
     * @param array $return
     * @return mixed
     */
    public function decorateResponse(Response $response, array $arguments, array $return);
}