<?php

/**
 * Description of Elastic
 *
 * @author PRADIP Humane
 * Email : pradip.humane123@gmail.com 
 */

class Elastic extends Curl {
    
    private $_protocol ='http';//ES protocol
    private $_host='';//Es host
    private $_port='';//ES Port
    private $_index='';//ES DB name
    private $_connectionurl='';//used to check ES connection
    private $_timeout=2;//Waiting time to connect with ES
    private $_isconnected =true;//store the connection boolean value.
    private $_request_url='';//request url as per the requirements (like PUT,GET,DELETE)
    
    public function __construct($host='127.0.0.1',$port='9200',$index='test') {        
        $this->_host = $host;
        $this->_port=$port;
        if(empty($this->_host))
        {
            throw new \Exception('Empty host found');
        }
        if(empty($this->_port))
        {
            throw new \Exception('Empty port found');
        }        
        $this->_isconnected=$this->_checkConnection();
        if(!$this->_isconnected)
        {
            throw new \Exception('Could not connect to elastic search server.');
        }
    }
    
    /*
     * Ping to elastic search URL and check connection is open or not.
     */
    public function _checkConnection()
    {
        $this->_connectionurl = $this->_protocol.'://'.$this->_host.':'.$this->_port;
        $start = microtime(true);
        $curl_obj = new Curl();       
        $httpResponse = $curl_obj->performHeadRequest($this->_connectionurl, $this->_timeout);
        $status_code = $curl_obj->getStatusCode($httpResponse);
        if($status_code == 200)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }        
    }
    
    
    /*
     * createDocument()
     * Create a new document or update document if exists.
     * @params 
     * @document : data in array which want to store in ES
     * @id : document unique id for this indices(DB)
     * @index : provide index name (DB)
     * @type : this is nothing but table in that index .
     */
    public function createDocument($document,$id,$index,$type)
    {
        $result = array();
        if($this->_isconnected)
        {
            $params['index']=$index;
            $params['id']=$id;
            $params['type']=$type;
            $this->_constructUrl($params);               
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "PUT",$document);
            }            
        }
        return $result;
    }
    /*
     * checkDocumentExists()
     * @docurl : document url
     */
    public function checkDocumentExists($params=array())
    {
        $result=FALSE;
        if($this->_isconnected)
        {
            $this->_constructUrl($params);               
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "HEAD",NULL);
            }   
            if(isset($result['code']) && $result['code']==200)
            {
                $result = TRUE;
            }
            else
            {
                $result = FALSE;
            }
        }
        return $result;
    }
    /*
     * _constructUrl()
     * @params array :provide all require parameters in array     * 
    */
   private function _constructUrl($params=array())
   {
       if($this->_isconnected)
       {
           $this->_request_url = $this->_protocol."://".$this->_host.":".$this->_port;
           $index = isset($params['index'])?$params['index']:'';
           $type = isset($params['type'])?$params['type']:'';
           $id = isset($params['id'])?$params['id']:'';
        //   $document = isset($params['document'])?$params['document']:false;
           $this->_request_url.="/".$index."/".$type;
           if(!empty($id))
           {
               $this->_request_url.="/".$id;
           }
           if(isset($params['update']))
           {
               $this->_request_url.="/".$params['update'];
           }
           if(isset($params['multiget']))
           {
               $this->_request_url.="/".$params['multiget'];
           }
           if(isset($params['count']))
           {
               $this->_request_url.="/".$params['count'];
           }
           if(isset($params['search']))
           {
               $this->_request_url.="/".$params['search'];
           }
           if(isset($params['search_type']) && !empty($params['search_type']))
           {
               $this->_request_url.="?search_type=".$params['search_type'];
           }
           if(isset($params['bulk']))
           {
               $this->_request_url.="/_bulk".$params['bulk'];
           }
           if(isset($params['size']) && !empty($params['size']))
           {
               $this->_request_url .= "?size=".$params['size'];
               if(isset($params['from']) && !empty($params['from']))
               {
                   $this->_request_url .= "&from=".$params['from'];
               }
           }
           if(isset($params['fields']) && !empty($params['fields']))
           {
               if(isset($params['size']) && !empty($params['size']))
               {
                    $this->_request_url .="&fields=".implode(',', $params['fields']);
               }
               else
               {
                   $this->_request_url .="?fields=".implode(',', $params['fields']);
               }
           }
           return $this->_request_url;
       }
       return FALSE;
   }
   
   /*
     * updateDocument()
     * @params 
     * @document : data in array which want to store in ES
     * @id : document unique id for this indices(DB)
     * @index : provide index name (DB)
     * @type : this is nothing but table in that index .
     */
   public function updateDocument($document,$id,$index,$type)
   {
       $result = array();
        if($this->_isconnected)
        {
            $params['index']=$index;
            $params['id']=$id;
            $params['type']=$type;
            $params['update']='_update';
            $this->_constructUrl($params);               
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "POST",$document);
            }            
        }
        return $result;
   }
   
    /*
     * deleteDocument() 
     * @params 
     * @id : document unique id for this indices(DB)
     * @index : provide index name (DB)
     * @type : this is nothing but table in that index .
     */
   public function deleteDocument($id,$index,$type)
   {
       $result = array();
        if($this->_isconnected)
        {
            $params['index']=$index;
            $params['id']=$id;
            $params['type']=$type;
            $this->_constructUrl($params);               
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "DELETE",NULL);
            }            
        }
        return $result;
   }
   
   /*
     * getDocument() 
     * @params 
     * @id : document unique id for this indices(DB)
     * @index : provide index name (DB)
     * @type : this is nothing but table in that index .
     */
   public function getDocument($id,$index,$type,$fields=array())
   {
       $result = array();
        if($this->_isconnected)
        {
            $params['index']=$index;
            $params['id']=$id;
            $params['type']=$type;
            if(!empty($fields))
            {
                $params['fields']=$fields;
            }
            $this->_constructUrl($params);               
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "GET",NULL);
            }            
        }
        return $result;
   }
   /*
     * getDocuments() 
     * Retrieve multiple documents
     * @params 
     * @document : data in array which want to store in ES
     * @index : provide index name (DB)
     * @type : this is nothing but table in that index .
     */
   public function getDocuments($document,$index,$type,$fields=array())
   {
       $result = array();
        if($this->_isconnected)
        {
            $params['index']=$index;
            //$params['id']=$id;
            $params['type']=$type;
            $params['multiget']="_mget";
            if(!empty($fields))
            {
                $params['fields']=$fields;
            }
            $this->_constructUrl($params);               
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "GET",$document);
            }            
        }
        return $result;
   }
   
   /*
    * getFilterdata
    * @filter=array
    * @range=array
    */
   public function getFilterdata($index,$type,$document,$limit=array(),$fields=array())
   {
       $result = array();      
        if($this->_isconnected)
        {
            $params['index']=$index;
            //$params['id']=$id;
            $params['type']=$type;
            $params['multiget']="_search";
            if(!empty($limit))
            {
                $params['size'] = $limit['max'];
                $params['from'] = $limit['from'];
            }
            if(!empty($fields))
            {
                $params['fields']=$fields;
            }
            $this->_constructUrl($params);
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "GET",$document);
            }            
        }
        return $result;
   }
   
   /*
    * getFilterdataCount()
    * 
    */
   public function getFilterdataCount($index,$type,$document)
   {
       $result = array();      
        if($this->_isconnected)
        {
            $params['index']=$index;
            //$params['id']=$id;
            $params['type']=$type;
            $params['count']="_count";
            $this->_constructUrl($params);
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "GET",$document);
            }            
        }
        return $result;
   }
   
   public function getFilterAggregations($index,$type,$document,$search_type)
   {
       $result = array();      
        if($this->_isconnected)
        {
            $params['index']=$index;
            //$params['id']=$id;
            $params['type']=$type;
            $params['search']="_search";
            $params['search_type']=$search_type;
            $this->_constructUrl($params);
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "GET",$document);
            }            
        }
        return $result;
   }
}
