<?php
namespace wmlib\controller;


interface IResponseDecorator
{
    /**
     * @param Response $response
     * @param $params
     * @return Response
     */
    public function decorateResponse(Response $response, $params);
}