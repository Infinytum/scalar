<?php

namespace Scalar\Router\Controller;

use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;

/**
 * Interface RestController
 * @package Scalar\Router\Controller
 *
 * Template for REST controllers
 */
interface RestController
{

    /**
     * REST API GET method
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $id Unique object identifier
     * @return ResponseInterface Returns array of objects if no id is specified, else return object with id
     */
    public function get
    (
        $request,
        $response,
        $id = null
    );

    /**
     * REST API POST method
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface Returns created object with it's new unique id
     */
    public function create
    (
        $request,
        $response
    );

    /**
     * REST API PUT
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $id Unique object identifier
     * @return ResponseInterface Returns updated object
     */
    public function update
    (
        $request,
        $response,
        $id = null
    );

    /**
     * REST API PATCH
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $id Unique object identifier
     * @return ResponseInterface Returns updated object
     */
    public function patch
    (
        $request,
        $response,
        $id = null
    );

    /**
     * REST API DELETE
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $id Unique object identifier
     * @return ResponseInterface Returns success or failure
     */
    public function delete
    (
        $request,
        $response,
        $id = null
    );

}