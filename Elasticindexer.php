<?php
/**
 * Description of Elasticindexer
 *
 * @author pradip humane
 * email : pradip.humane123@gmail.com
 */

class Elasticindexer {
    
    private $_protocol ='http';//ES protocol
    private $_host='';//Es host
    private $_port='';//ES Port
    private $_index='';//ES DB name
    private $_connectionurl='';//used to check ES connection
    private $_timeout=2;//Waiting time to connect with ES
    private $_isconnected =true;//store the connection boolean value.
    private $_request_url='';//request url as per the requirements (like PUT,GET,DELETE)
    private $elastic_obj='';
    
    /*
    * __construct()
    * @param $host
    * @param $port
    * Desc: connected to elastic server if not connected then it shows error message.
    */
    public function __construct($host='127.0.0.1',$port='9200') {
        $this->elastic_obj = new Elastic($host,$port);
        $this->_isconnected = $this->elastic_obj->_checkConnection();
    }
    /*
    * createDocument()
    * @param $document, actual data array which is used to create document
    * @param $id, document unique id 
    * @param $index, name of collection same as like DB name
    * @param $type, name of sub collection just as like mysql/sql table
    * Desc : Create document with specified index/type name with unique id
    */
    public function createDocument($document,$id,$index="test",$type="member")
    {
        $result = array();
        if($this->_isconnected)
        {
           $result= $this->elastic_obj->createDocument(json_encode($document), $id, $index, $type);           
        }
        return $result;
    }
    /*
    * getDocument()
    * @param $id, single document id 
    * @param $fields, list of fields which are going display in o/p
    * @param $index, name of collection same as like DB name
    * @param $type, name of sub collection just as like mysql/sql table
    * Desc : get document for specified document id 
    */
    public function getDocument($id,$fields=array(),$index="test",$type="member")
    {
        $output=array();
        $cnt=0;
        $result = array();
        $key = "_source";
        if(!empty($fields))
        {
            $key = "fields";
        }
        if($this->_isconnected)
        {
           $result= $this->elastic_obj->getDocument($id, $index, $type,$fields); 
           if(!empty($result))
           {
               if(isset($result[$key])){                  
                 $output['data'][]=$result[$key];                   
               }
           }
        }
        $output['total']=1;
        return $output;
    }
    /*
    * getDocuments()
    * @param $id, multiple document ids 
    * @param $fields, list of fields which are going display in o/p
    * @param $index, name of collection same as like DB name
    * @param $type, name of sub collection just as like mysql/sql table
    * Desc : get documents for specified document ids 
    */
    public function getDocuments($document,$fields=array(),$index="test",$type="member")
    {
        $result = array();
        $output=array();
        $cnt = 0;
        $key = "_source";
        if(!empty($fields))
        {
            $key = "fields";
        }
        if($this->_isconnected)
        {
           $result= $this->elastic_obj->getDocuments($document,$index,$type,$fields); 
           if(!empty($result))
           {
               if(isset($result['docs'])){
                   foreach($result['docs'] as $val)
                   {
                       $cnt++;
                       $output['data'][]=$val[$key];
                   }
               }
           }
        }
        $output['total']=$cnt;
        return $output;
    }
    /*
    * updateDocument()
    * @param $document, array of fields with values which are going to update in document 
    * @param $id, document unique id 
    * @param $index, name of collection same as like DB name
    * @param $type, name of sub collection just as like mysql/sql table
    * Desc : update document for specified document id  
    */    
   public function updateDocument($document,$id,$index="test",$type="member")
   {
       $result = FALSE;
        if($this->_isconnected)
        {               
            if(!empty($document) && !empty($id))
            {
                $documents['doc']=$document;
                $result =  $this->elastic_obj->updateDocument(json_encode($documents), $id, $index, $type);
                if(!empty($result))
                {
                    $result = TRUE;
                }
            }            
                      
        }
        return $result;
   }
   /*
    * deleteDocument()
    * @param $id, document unique id 
    * @param $index, name of collection same as like DB name
    * @param $type, name of sub collection just as like mysql/sql table
    * Desc : delete single document for specified document id 
    */    
   public function deleteDocument($id,$index="test",$type="member")
   {
       $result = false;
        if($this->_isconnected)
        {
            if(!empty($id))
            {
                $result =  $this->elastic_obj->deleteDocument($id, $index, $type);
                if(!empty($result))
                {
                    $result = TRUE;
                }
            }
        }
        return $result;
   }
   /*
    * isDocumentExists()
    * @param $id, document unique id 
    * @param $index, name of collection same as like DB name
    * @param $type, name of sub collection just as like mysql/sql table
    * Desc : check whether document is exists or not in specified index or type 
   */   
   public function isDocumentExists($id,$index="test",$type="member")
   {
       $result = false;
        if($this->_isconnected)
        {
            if(!empty($id))
            {
                $params['index'] =$index;
                $params['type']=$type;
                $params['id']=$id;
                $result =  $this->elastic_obj->checkDocumentExists($params);              
            }
        }
        return $result;
   }
   /*
    * Filter()
    * @param $index, name of collection same as like DB name
    * @param $type, name of sub collection just as like mysql/sql table
    * @param $op, operator default operator is AND
    * @param $filter, this is used for filtered query format of elastic search
    * @param $query, this is elastic search query format
    * @param $limit, contains from and max limit. num of of records needs to fetch
    * @param $fields, list of fields which needs to be shown
    * @param $sort, mentioned sort field name.
    * Desc : get all the result as per filter
    */
   public function Filter($index="test",$type="member",$op="AND",$filter=array(),$query=array(),$limit=array(),$fields=array(),$sort=array())
   {
       
       $result = false;
        if($this->_isconnected)
        {
               $filter_query=array();
                $params['index'] =$index;
                $params['type']=$type;
                
                if(!empty($filter))
                {
                    $filter_query['query']['filtered']['filter'] = $filter;
                }
                if(!empty($query))
                {
                    $filter_query['query']['filtered']['query'] = $query;
                }
                if(!empty($query))
                {
                    $filter_query['sort'] = $sort;
                }
                if(empty($limit))
                {
                    $limit['max']=10;
                    $limit['from']=0;
                }
                //pr($filter_query);exit;
                $result =  $this->elastic_obj->getFilterdata($index,$type,$filter_query,$limit,$fields);              
            
        }
        $final_result=array();
        $key = "_source";
        if(!empty($fields))
        {
            $key = "fields";
        }
        if(!empty($result))
        {
            if(!empty($result['hits']))
            {
                $final_result['total']=$result['hits']['total'];
                for($i=0;$i<$result['hits']['total'];$i++)
                {
                    $final_result['data'][]=$result['hits']['hits'][$i][$key];
                }
            }
        }
        //pr($result);exit;
        return $final_result;
   }
   
   /*
    * FilterResultCount()
    * @param $index, name of collection same as like DB name
    * @param $type, name of sub collection just as like mysql/sql table
    * @param $op, operator default operator is AND
    * @param $filter, this is used for filtered query format of elastic search
    * @param $query, this is elastic search query format
    * @param $range, optinal no need to use
    * Desc : get result count
    */
   public function FilterResultCount($index="test",$type="member",$op="AND",$filter=array(),$query=array(),$range=array())
   {
       
       $result = false;
        if($this->_isconnected)
        {
            $params['index'] =$index;
            $params['type']=$type;
            $filter_query['query']['filtered'] = array(
                'filter'=>$filter,
                'query'=>$query
            );
            $result =  $this->elastic_obj->getFilterdataCount($index,$type,$filter_query); 
        }
        
        //pr($result);exit;
        return $result['count'];
   }
   /*
    * FilterAggregations()
    * @param $index, name of collection same as like DB name
    * @param $type, name of sub collection just as like mysql/sql table
    * @param $aggregation_name, name of filed on which we want to set aggregation result
    * @param $filter, this is used for filtered query format of elastic search
    * @param $search_type, basically name of aggregate function 
    * @param $range, optinal no need to use
    * Desc : return aggregations result,it is kind of group by( like Facets)
    */
   public function FilterAggregations($index="test",$type="member",$aggregation_name='',$filter=array(),$search_type="",$filter_fields=array())
   {       
       $result = false;
        if($this->_isconnected)
        {
            $params['index'] =$index;
            $params['type']=$type;
            $filter_query['aggregations'][$aggregation_name]['terms']= $filter;
            if(!empty($filter_fields))
            {
                foreach ($filter_fields as $k=>$v)
                {
                    $filter_query['query']['filtered']['filter'][]['term']=array($k=>$v);
                }
            }
            $result =  $this->elastic_obj->getFilterAggregations($index,$type,$filter_query,$search_type); 
        }
        $output=array();
        if(!empty($result))
        {            
            if(isset($result['aggregations'][$aggregation_name]['buckets']) && !empty($result['aggregations'][$aggregation_name]['buckets']))
            {
                $count = count($result['aggregations'][$aggregation_name]['buckets']);
                for($i=0;$i<$count;$i++)
                {
                    $output[$result['aggregations'][$aggregation_name]['buckets'][$i]['key']] = $result['aggregations'][$aggregation_name]['buckets'][$i]['doc_count'];
                }
            }
        }
        //pr($output);exit;
        return $output;
   }
   /**
    * @developer Mayur Takawale & Pradip humane
    * @description to perform bulk operations
    * @param array $document contain data to add in elasetic search. For delete operation data is not required 
    * @param string $index index name on which operation get execute
    * @param string $type type name on which operation get execute
    */
   public function bulk($document,$index="test",$type="member")
   {
        $result = array();
        if($this->_isconnected)
        {
           if(!empty($document))
           {
               $bulkString = array();
               foreach ($document as $key => $value)
               {
                   $doc_id = $key;
                   $action = $value['action'];
                   $bulkString[] = json_encode(array($action=>array('_id'=>$doc_id)));
                   $bulkString[] = json_encode($value['request']);
               }
               if(!empty($bulkString)){
                   $bulkString = join("\n", $bulkString)."\n";               
                   $result= $this->elastic_obj->bulkDocument($index, $type,$bulkString); 
               }
           }                     
        }
        return $result;
   }
   /*
    * Function : createMapping()
    * @param $index name of index like DB
    * @param $type name of type which is created under index
    * @param $document document array format for mapping
    * Desc : Mapping is used for defined schema, like user can create emp type with defined schema with datatypes
    */
   public function createMapping($document,$index="test",$type="member")
   {
       $result = array();
        if($this->_isconnected)
        {
           if(!empty($document))
           {
               $result = $this->elastic_obj->createMapping($index,$type, $document);
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
           $result = $this->elastic_obj->getMapping($index,$type);           
        }
        return $result;
   }
   /*
    * createIndex()
    * @document, settings array 
    * @index, name of index which we are going to create
    * Desc : create index in ES with all settings
    */
   public function createIndex($settings,$index='test')
   {
        $result = array();
        if($this->_isconnected && !empty($settings))
        {
           $settings['settings'] = $settings; 
           $result = $this->elastic_obj->createIndex($settings, $index);           
        }
        return $result;
   }
}
