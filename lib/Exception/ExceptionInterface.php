<?php
namespace wmlib\controller\Exception;

interface ExceptionInterface
{

    public function getStatusCode();

    public function getHeaders();
}