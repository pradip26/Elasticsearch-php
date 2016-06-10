<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Curl
 *
 * @author PRADIP Humane
 * Email : pradip.humane123@gmail.com
 */
include 'Curlresponse.php';
class Curl extends Curlresponse
{
	/**
	 * Curl Session Handle
	 *
	 * @var resource
	 */
	private $_curl;

	/**
	 * Initializes a curl session
	 */
	public function __construct()
	{
		// initialize a CURL session
		$this->_curl = curl_init();

		// set common options that will not be changed during the session
		curl_setopt_array($this->_curl, array(
			// return the response body from curl_exec
			CURLOPT_RETURNTRANSFER => true,

			// get the output as binary data
			CURLOPT_BINARYTRANSFER => true,

			// we do not need the headers in the output, we get everything we need from curl_getinfo
			CURLOPT_HEADER => false
		));
	}

	/**
	 * Closes a curl session
	 */
	function __destruct()
	{
		// close our curl session
		//curl_close($this->_curl);
	}
	
	public function setAuthenticationCredentials($username, $password)
	{
		// add the options to our curl handle
		curl_setopt_array($this->_curl, array(
			CURLOPT_USERPWD => $username . ":" . $password,
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC		
		));
	}

	public function performGetRequest($url, $timeout = false)
	{
		// check the timeout value
		if ($timeout === false || $timeout <= 0.0)
		{
			// use the default timeout
			$timeout = $this->getDefaultTimeout();
		}

		// set curl GET options
		curl_setopt_array($this->_curl, array(
			// make sure we're returning the body
			CURLOPT_NOBODY => false,

			// make sure we're GET
			CURLOPT_HTTPGET => true,

			// set the URL
			CURLOPT_URL => $url,

			// set the timeout
			CURLOPT_TIMEOUT => $timeout
		));

		// make the request
		$responseBody = curl_exec($this->_curl);
                var_dump($responseBody);
		// get info from the transfer
		$statusCode = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
                var_dump($statusCode);
		$contentType = curl_getinfo($this->_curl, CURLINFO_CONTENT_TYPE);

		return new Curlresponse($statusCode, $contentType, $responseBody);
	}

	public function performHeadRequest($url, $timeout = false)
	{
		// check the timeout value
		if ($timeout === false || $timeout <= 0.0)
		{
			// use the default timeout
			$timeout = $this->getDefaultTimeout();
		}

		// set curl HEAD options
		curl_setopt_array($this->_curl, array(
			// this both sets the method to HEAD and says not to return a body
			CURLOPT_NOBODY => true,

			// set the URL
			CURLOPT_URL => $url,

			// set the timeout
			CURLOPT_TIMEOUT => $timeout
		));

		// make the request
		$responseBody = curl_exec($this->_curl);

		// get info from the transfer
		$statusCode = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
		$contentType = curl_getinfo($this->_curl, CURLINFO_CONTENT_TYPE);

		return new Curlresponse($statusCode, $contentType, $responseBody);
	}

	public function performPostRequest($url, $postData, $contentType, $timeout = false)
	{
		// check the timeout value
		if ($timeout === false || $timeout <= 0.0)
		{
			// use the default timeout
			$timeout = $this->getDefaultTimeout();
		}

		// set curl POST options
		curl_setopt_array($this->_curl, array(
			// make sure we're returning the body
			CURLOPT_NOBODY => false,

			// make sure we're POST
			CURLOPT_POST => true,

			// set the URL
			CURLOPT_URL => $url,

			// set the post data
			CURLOPT_POSTFIELDS => $postData,

			// set the content type
			CURLOPT_HTTPHEADER => array("Content-Type: {$contentType}"),

			// set the timeout
			CURLOPT_TIMEOUT => $timeout
		));

		// make the request
		$responseBody = curl_exec($this->_curl);

		// get info from the transfer
		$statusCode = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
		$contentType = curl_getinfo($this->_curl, CURLINFO_CONTENT_TYPE);

		return new Curlresponse($statusCode, $contentType, $responseBody);
	}
        
        /**
	 * Get the status code of the response
	 *
	 * @return integer
	 */
	public function getStatusCode($httpResponse)
	{
		return $httpResponse->_statusCode;
	}
	
	/**
	 * Get the status message of the response
	 *
	 * @return string
	 */
	public function getStatusMessage($httpResponse)
	{
		return $httpResponse->_statusMessage;
	}
	
	/**
	 * Get the mimetype of the response body
	 *
	 * @return string
	 */
	public function getMimeType($httpResponse)
	{
		return $httpResponse->_mimeType;
	}
	
	/**
	 * Get the charset encoding of the response body.
	 *
	 * @return string
	 */
	public function getEncoding($httpResponse)
	{
		return $httpResponse->_encoding;
	}
	
	/**
	 * Get the raw response body
	 *
	 * @return string
	 */
	public function getBody($httpResponse)
	{
		return $httpResponse->_responseBody;
	}
        
        /**
     * Perform a http call against an url with an optional payload
     *
     * @return array
     * @param string $url
     * @param string $method (GET/POST/PUT/DELETE)
     * @param array|bool $payload The document/instructions to pass along
     * @throws HTTPException
     */
    public function call($url, $method="GET", $payload=null) {
        $conn = $this->_curl;
        $requestURL = $url;
        if(empty($conn))
        {
            $conn = curl_init();
        }
        curl_setopt($conn, CURLOPT_URL, $requestURL);
        //curl_setopt($conn, CURLOPT_TIMEOUT, $this->timeout);
        //curl_setopt($conn, CURLOPT_PORT, $this->port);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1) ;
        curl_setopt($conn, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($conn, CURLOPT_FORBID_REUSE , 0) ;

        if (is_array($payload) && count($payload) > 0)
            curl_setopt($conn, CURLOPT_POSTFIELDS, json_encode($payload)) ;
        else
	       	curl_setopt($conn, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($conn);
        if ($response !== false) {
            $data = json_decode($response, true);
            if (!$data) {
                $data = array('error' => $response, "code" => curl_getinfo($conn, CURLINFO_HTTP_CODE));
            }
        }
        return $data;
    }
}
