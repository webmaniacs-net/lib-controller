<?php
namespace wmlib\controller\Response;

use wmlib\controller\Response;
use wmlib\controller\Url;

class Factory
{

    /**
     * @param Response $response
     * @param $url
     * @return Response
     */
    public static function Redirect(Response $response, $url)
    {
        $request = $response->getRequest();

        $redirect_uri = $request->getBaseUrl()->resolve(is_string($url) ? new Url($url) : $url);

        if ($request->isPost() && $request->isXHR()) {
            /**
             * Add Ajax modifier
             */
            $redirect_uri = $redirect_uri->withQueryValue('ajax', 'yes');
        }

        return $response
            ->withStatus(Response::STATUS_FOUND)
            ->withHeader(Response::HEADER_LOCATION, $redirect_uri->__toString());
    }
}