<?php
namespace wmlib\controller;

use Psr\Http\Message\StreamInterface;

interface IStreamable extends StreamInterface
{
    /**
     * @return string
     */
    public function hashCode();
}