<?php
namespace Paymill\Paymill\Services\Apiclient;

interface InterfaceCurl
{
    const HTTP_POST = 'POST';
    const HTTP_GET  = 'GET';
    const HTTP_PUT  = 'PUT';
    const HTTP_DELETE  = 'DELETE';

    /**
     * Perform API and handle exceptions
     *
     * @param $action
     * @param array $params
     * @param string $method
     * @return mixed
     */
    public function request($action, $params = array(), $method = 'POST');

}
