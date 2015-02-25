<?php
/**
 * Services_Linode
 *
 * PHP Version 5
 *
 * Copyright (c) 2010, Kerem Durmus
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice, 
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, 
 *   this list of conditions and the following disclaimer in the documentation 
 *   and/or other materials provided with the distribution.
 * - Neither the name of the Digg, Inc. nor the names of its contributors 
 *   may be used to endorse or promote products derived from this software 
 *   without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package  Services_Linode
 * @category Services
 * @author   Kerem Durmus <kerem@keremdurmus.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License  
 * @version  @package_version@
 * @link     http://github.com/krmdrms/linode/
 * @link     http://www.linode.com/api/autodoc.cfm
 */

require_once 'HTTP/Request2.php'; 
require_once 'Services/Linode/Exception.php';

/**
 * Services_Linode
 *
 * @package  Services_Linode
 * @category Services
 * @author   Kerem Durmus <kerem@keremdurmus.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License  
 * @version  @package_version@
 * @link     http://github.com/krmdrms/linode/
 * @link     http://www.linode.com/api/autodoc.cfm
 */
class Services_Linode
{
    /**
     * Request Url
     *
     * @var string $apiUrl
     */
    private static $apiUrl = 'https://api.linode.com/';
    
    /**
     * Default HTTP_Request2 config parameters
     *
     * @var array
     */     
    private $httpConfig = array('ssl_verify_peer' => false);
    
    /**
     * Instance of {@link HTTP_Request2}
     *
     * @var object $request
     */     
    protected $request;    
    
    /**
     * API mapping. Constructed by api.xml
     *
     * @var array $api
     */
    protected $api = array();
       
    /**
     * Request array which will send to Linode Api
     *
     * @var array $requestArray
     */
    protected $requestArray = array();
    
    /**
     * Default API Response format
     *
     * @var string $responseFormat
     */
    protected $responseFormat = 'json';
    
    /**
     * Cache for batching request
     * 
     * @var array $batchCache
     */
    protected $batchCache = array();
    
    /**
     * Linode API key
     *
     * @var string $api_key
     */
    public $apiKey;
    
    /**
     * Batching request
     *
     * @var bool
     */
    public $batching = false;
    
    /**
     * Constructor
     *
     * @param  string $api_key Your linode api key
     * @param  boolean $batch Enables batch request
     * @return void
     */
    public function __construct($apiKey = null, $batching = false)
    {
        if(!isset($apiKey)) {
            throw new Services_Linode_Exception('You must set your api key');
        }
        $this->apiKey = $apiKey;
        $this->batching = $batching;
        $this->loadAPI();
    }
    
    /**
     * Overloading method to call given api method and parameters
     *
     * @param string $method
     * @param array  $args
     * @return array
     */
    public function __call($method, array $args = array())
    {
        $method = strtolower(str_replace('_','.',$method));        
        
        if (isset($this->api[$method])) {    
        
            list($method,$params) = $this->prepareRequest($this->api[$method],$args);
            
            if($this->batching == true) {
                $params['api_action'] = $method;
                $this->cacheParams($params);
            } else {
                $response = $this->sendRequest($method, $params);  
                return $this->decodeBody($response);     
            }
            
        } else {      
            throw new Services_Linode_Exception('Unknown method: '.$method);
        }
    }
    
    /**
     * Sends cached api requests to api
     *
     * @return array
     */    
    public function batchFlush()
    {
        $this->setParam('api_requestArray', json_encode($this->batchCache));
        $response = $this->sendRequest('batch');
        
        return $this->decodeBody($response);
    }    
    
    /**
     * Cache api requests when batching enabled
     *
     * @var array $params
     * @return void
     */    
    protected function cacheParams($params) 
    {
        $this->batchCache[] = $params;
    }
        
    /**
     * Sets api request parameter
     *
     * @param  string $key
     * @param  string $value 
     * @return void
     */    
    protected function setParam($key,$value)
    {
        $this->requestArray[$key] = $value;
    }
    
    /**
     * Returns set parameters
     *
     * @return array
     */    
    protected function getParams()
    {
       return $this->requestArray;
    }
     
    /**
     *  Returns the HTTP_Request2 instance.
     *
     * @return object
     */
    protected function httpRequest()
    {
        if ($this->request === null) {
            $this->request = new HTTP_Request2();
            $this->request->setConfig($this->httpConfig);    
            $this->request->setMethod(HTTP_Request2::METHOD_POST);
            $this->request->setUrl(self::$apiUrl);            
            $this->request->setHeader(array('User-Agent' => 'Linode PHP/@package_version@'));
        }
        
        return $this->request;
    }
    
    /**
     * Send request to api
     *
     * @var $method
     * @var $params
     * @return array
     */
    protected function sendRequest($method, $params = null)
    {
    
        $this->setParam('api_key', $this->apiKey);
        $this->setParam('api_responseFormat', $this->responseFormat);
        $this->setParam('api_action', $method);

        if($this->batching == false) {
            foreach($params as $param => $value) {
                   $this->setParam($param,$value);
            } 
        }
             
        try {
            $request = clone $this->httpRequest();
            $request->addPostParameter($this->requestArray);
            $response = $request->send();
        } catch(HTTP_Request2_Exception $e) {
            throw new Services_Linode_Exception($e->getMessage());
        }
        
        return $response->getBody();
    }
    
    /**
     * Decodes body
     *
     * @var string $body
     * @return array
     */    
    protected function decodeBody($body) 
    {
        if($this->responseFormat = 'json') {
            $decoded_body = json_decode($body, TRUE);
        } else {
            $decoded_body = $body;
        }
        
        return $decoded_body;
    }    
    
    /**
     * Validates argument and prepare to request
     *
     * @var $method
     * @var $args
     * @return array
     */
    protected function prepareRequest($method, array $args = array())
    {
        $count_args = count($method->xpath('param[@required="true" or @required="1"]'));
    
        $path = (string) $method['name'];
        $params = array();
    
        if ($count_args && (!isset($args[0]) || is_array($args[0]) && $count_args > count($args[0]))) {
            throw new Services_Linode_Exception(
                'Not enough arguments for '.$path
            );
        }
        $req_args = $count_args;
    
        foreach($method->param as $param) {
            $name = (string) $param['name'];
            $type = (string) $param['type'];
            $required =  (string) $param['required'] == 'true'  || $req_args;
                        
            if ($required && !is_array($args[0])) {
                $arg = array_shift($args);
                $req_args--;
            } else if (isset($args[0][$name])) {
                $arg = $args[0][$name];
                $req_args--;
            } else {
              continue;
            }
             
            try {
                $this->validateArg($name, $arg , $type); 
            } catch (Services_Linode_Exception $e) {
                echo $e->getMessage();
            }
            
            $params[$name] = $arg;
        }
        return array($path,$params);
    }
    
    /**
     * Validates an argument according to api xml mapping
     *
     * @var $name
     * @var $val
     * @var $type
     * @return void
     */  
    protected function validateArg($name, &$val, $type) 
    {    
        $msg = null;
        switch ($type) {
            case 'boolean':
                if (!is_bool($val)) {
                    $msg = $name . ' must be a boolean';
                }
                $val = $val ? 'true' : 'false';
                break;
            case 'integer':
                if (!is_numeric($val)) {
                    $msg = $name . ' must be an integer';
                }
                break;
            case 'string':
                if (!is_string($val)) {
                    $msg = $name . ' must be a string';
                }
                break;        
        }
        
        if ($msg !== null) {
            throw new Services_Linode_Exception($msg);
        }
    }    
    
    /**
     * Loads the XML API definition.
     *
     * @return void
     */
    protected function loadAPI()
    {
		$filePath = dirname(__FILE__) . "/Services_Linode/";
		$feed = file_get_contents($filePath.'api.xml');
		$xmlApi = simplexml_load_string($feed);
        foreach ($xmlApi->method as $method) {
            $method_name = (string) $method['name'];
            $this->api[$method_name] =  $method;
        }
    }
}