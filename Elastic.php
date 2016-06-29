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
    
    /*
    * __construct()
    * @param $host
    * @param $port
    * @param $index
    * Desc: we are connecting to elastic search server
    */
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
     *_checkConnection() 
     * Ping to elastic search server through URL and check connection is open or not.
     * if connection is not opened then return False else true.
     */
    private function _checkConnection()
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
     * Check document is exists or not if exists then return True else false.
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
     * @params array :provide all require parameters in array
     * Create final curl URL which are going to hit to elastic server basis on request .
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
           if(!empty($params['mapping']))
           {
               $this->_request_url.="/".$index."/".$params['mapping']."/".$type;
           }
           else
           {
                $this->_request_url.="/".$index."/".$type;
           }
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
               $this->_request_url.="/".$params['bulk'];
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
     * @param $document : data in array which want to store in ES
     * @param $id : document unique id for this indices(DB)
     * @param $index : provide index name (DB)
     * @param $type : this is nothing but table in that index .
     * Desc: Update created documents 
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
     * @param $id : document unique id for this indices(DB)
     * @param $index : provide index name (DB)
     * @param $type : this is nothing but table in that index .
     * Desc : Delete document from index.
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
     * @param $id : document unique id for this indices(DB)
     * @param $index : provide index name (DB)
     * @param $type : this is nothing but table in that index .
     * Desc : get document by id in index or also get the specific fields which are passing in array
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
     * @param $document : data in array which want to store in ES
     * @param $index : provide index name (DB)
     * @param $type : this is nothing but table in that index .
     * Desc : get document by multiple ids in index or also get the specific fields which are passing in array
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
    * getFilterdata()
    * @param $filter=array
    * @param $range=array
    * Desc: passed filter Document array in json with operator and get respective results
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
    * @param $index
    * @param $type
    * @param $document
    * Desc: only get the filter results data count
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
   /*
   * getFilterAggregations()
   * @param $index
   * @param $type
   * @param $document
   * @param $search_type, like count, sum, avg
   * Desc:  provide aggregated data based on a search query
   */
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
   /*
    * bulkDocument()
    * Desc : used to update bulk document, user can perform multiple actions in single request
    */
   public function bulkDocument($index,$type,$document)
   {
        $result = array();      
        if($this->_isconnected)
        {
            $params['index']=$index;
            $params['type']=$type;
            $params['bulk']="_bulk";
            $this->_constructUrl($params);
            echo $this->_request_url;
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "POST",$document);
            }            
        }
        return $result;
   }
   /*
    * Function : createMapping()
    * @param $index name of index like DB
    * @param $document document array 
    * Desc : Mapping is used for defined schema with fields and datatype 
    */
   public function createMapping($index,$type,$document)
   {
       $result = array();      
        if($this->_isconnected)
        {
            $params['index']=$index;
            $params['type']=$type;
            $params['mapping']="_mapping";
            $this->_constructUrl($params);
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "PUT",$document);
            }            
        }
        return $result;
   }
   /*
    * Function : getMapping()
    * @param $index name of index like DB
    * @param $type
    * Desc : get mapping of index
    */
   public function getMapping($index,$type)
   {
       $result = array();      
        if($this->_isconnected)
        {
            $params['index']=$index;
            $params['type']=$type;
            $params['mapping']="_mapping";
            $this->_constructUrl($params);
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "GET");
            }            
        }
        return $result;
   }
   /*
    * createIndex()
    * @index, name of index which we are going to create
    * Desc : create index in ES with all settings
    */
   public function createIndex($document,$index='test')
   {
        $result = array();      
        if($this->_isconnected)
        {
            $this->_request_url = $this->_protocol."://".$this->_host.":".$this->_port."/".$index;
            if(!empty($this->_request_url))
            {
               $result =  $this->call($this->_request_url, "PUT",$document);
            }            
        }
        return $result;
   }
}
