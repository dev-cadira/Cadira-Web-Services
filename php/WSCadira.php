<?php

/*
Copyright (c) 2022, Cadira, All rights reserved.
This library is free software; you can redistribute it and/or modify it under the 
terms of the GNU Lesser General Public License as published by the 
Free Software Foundation; either version 3.0 of the License, or (at your option) 
any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library.

This library is based on the code base of the 
https://github.com/jaimey/vtiger-restapi-php
*/

namespace Cadira;

class Cadira
{
    public $serveraddress;
    public $userName;
    public $userAccessKey;
    public $sessionName;
    
    /**
     * __construct
     *
     * @param  String $serveraddress
     * @param  String $userName
     * @param  String $userAccessKey
     * @return void
     */
    public function __construct($serveraddress, $userName, $userAccessKey)
    {
        $this->serveraddress = $serveraddress . "/webservice.php";
        $this->userName = $userName;
        $this->userAccessKey = $userAccessKey;
        $this->login();
    }
    
    /**
     * getToken
     * Get connection token 
     * @return String
     */
    private function getToken()
    {
        $data = [
            'operation' => 'getchallenge',
            'username'  => $this->userName
        ];

        $token_data = $this->sendHttpRequest($data, 'GET');
        return $token_data->result->token;
    }
    
    /**
     * login
     * Login to the platform
     * @return void
     */
    private function login()
    {
        $token = $this->getToken();
        $data = array(
            'operation' => 'login',
            'username'  => $this->userName,
            'accessKey' => md5($token . $this->userAccessKey),
        );
        $result = $this->sendHttpRequest($data, 'POST');
        $this->sessionName = $result->result->sessionName;
    }
    
    /**
     * create
     * Crear registro entidad
     * @param  Array $params
     * @param  String $module
     * @return void
     */
    public function create($params, $module)
    {
        $element = json_encode($params);
        $data = array(
            'operation'   => 'create',
            'sessionName' => $this->sessionName,
            'element'     => $element,
            'elementType' => $module
        );
        return $this->sendHttpRequest($data, 'POST');
    }
    
    /**
     * update
     * Update entity registration
     * @param  Array $params
     * @return Object
     */
    public function update($params)
    {
        $element = json_encode($params);
        $data = array(
            'operation'   => 'update',
            'sessionName' => $this->sessionName,
            'element'     => $element
        );
        return $this->sendHttpRequest($data, 'POST');
    }
    
    /**
     * retrieve
     * Allows you to retrieve the structure of a record
     * @param  String $id
     * @return Object
     */
    public function retrieve($id)
    {
        $data = array(
            'operation'     => 'retrieve',
            'sessionName'   => $this->sessionName,
            'id'            => $id
        );
        return $this->sendHttpRequest($data, 'GET');
    }
    
    /**
     * revise
     * Allows you to obtain the structure of a record and then update its values.
     * @param  Array $params
     * @return Object
     */
    public function revise($params)
    {
        $element = json_encode($params);

        $data = array(
            'operation'     => 'revise',
            'sessionName'   => $this->sessionName,
            'element'       => $element
        );
        return $this->sendHttpRequest($data, 'POST');
    }
    
    /**
     * describe
     * Describe the structure of a register
     * @param  String $module
     * @return Object
     */
    public function describe($module)
    {
        $data = array(
            'operation'     => 'describe',
            'sessionName'   => $this->sessionName,
            'elementType'   => $module
        );
        return $this->sendHttpRequest($data, 'GET');
    }
    
    /**
     * listTypes
     * List entities and record types
     * @return Object
     */
    public function listTypes()
    {
        $data = array(
            'operation'     => 'listtypes',
            'sessionName'   => $this->sessionName
        );
        return $this->sendHttpRequest($data, 'GET');
    }
    
    /**
     * retrieveRelated
     * Get the records related to a record
     * @param  String $id
     * @param  String $targetLabel
     * @param  String  $targetModule
     * @return Object
     */
    public function retrieveRelated($id, $targetLabel, $targetModule)
    {
        $data = array(
            'operation'     => 'retrieve_related',
            'sessionName'   => $this->sessionName,
            'id'            => $id,
            'relatedLabel'  => $targetLabel,
            'relatedType'   => $targetModule,
        );
        return $this->sendHttpRequest($data, 'GET');
    }
    
    /**
     * query
     * Allows to execute queries in vtiger format
     * @param  String $module
     * @param  Array $params
     * @param  Array $select
     * @return Object
     */
    public function query($module, $params, $select = [])
    {
        $query = $this->getQueryString($module, $params, $select);
        $data = array(
            'operation'     => 'query',
            'sessionName'   => $this->sessionName,
            'query'         => $query
        );
        return $this->sendHttpRequest($data, 'GET');
    }
    
    /**
     * getQueryString
     * Encapsulates query execution
     * @param  String $module
     * @param  Array $params
     * @param  Array $select
     * @return Object
     */
    private function getQueryString($moduleName, $params, $select = [])
    {
        $criteria = array();
        $select = (empty($select)) ? '*' : implode(',', $select);
        $query = sprintf("SELECT %s FROM $moduleName", $select);

        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $criteria[] = "{$param} = '{$value}'";
            }

            $query .= sprintf(' WHERE %s ;', implode(" AND ", $criteria));
        }
        return $query;
    }
    
    /**
     * sendHttpRequest
     * Allows sending GET and POST requests
     * @param  Array $data
     * @param  String $method
     * @return Object
     */
    public function sendHttpRequest($data, $method)
    {
        $client = new \GuzzleHttp\Client();

        switch ($method) {
            case 'GET':
                $response = $client->request('GET', $this->serveraddress, ['query' => $data])->getBody();
                break;

            case 'POST':
                $response = $client->request('POST', $this->serveraddress, ['form_params' => $data])->getBody();
                break;
        }
        $response = json_decode($response);
        if (!$response->success) {
            throw new \Exception($response->error->code . ": " . $response->error->message);
        }
        return $response;
    }
}
