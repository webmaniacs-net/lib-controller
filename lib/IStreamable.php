<?php
namespace wmlib\controller;

use Psr\Http\Message\StreamableInterface;

interface IStreamable extends StreamableInterface
{
    /**
     * @return string
     */
    public function hashCode();
}