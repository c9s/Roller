<?php
namespace Roller\Plugin\RESTful;
use Roller\Controller;


abstract class BaseHandler extends Controller
{
    public $message;

    public $data;

    public function __construct()
    {
        $this->init();
        if( false === $this->authenticate() ) {
            $this->codeForbidden();
            die('Permission Denied.');
        }
    }


    /**
     * init function
     */
    public function init()
    {

    }


    public function authenticate()
    {
        return true;
    }

    /**
     * HTTP Status Code:
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * @link http://restpatterns.org/HTTP_Status_Codes
     *
     * REST Pattern
     * @link http://restpatterns.org/
     */
    public function codeOk() 
    {
        header("HTTP/1.1 200 OK");
    }

    public function codeCreated()
    {
        header('HTTP/1.1 201 Created');
    }

    public function codeAccepted()
    {
        header('HTTP/1.1 202 Accepted');
    }

    public function codeNoContent() 
    {
        header('HTTP/1.1 204 No Content');
    }

    public function codeBadRequest()
    {
        header('HTTP/1.1 400 Bad Request');
    }

    public function codeForbidden()
    {
        header('HTTP/1.1 403 Forbidden');
    }

    public function codeNotFound()
    {
        header('HTTP/1.1 404 Not Found');
    }

    public function getClass()
    {
        return get_class($this);
    }
}
