<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
    public function __construct($host='127.0.0.1',$port='9200') {
        $this->elastic_obj = new Elastic($host,$port);
        $this->_isconnected = $this->elastic_obj->_checkConnection();
    }
    
    public function createDocument($document,$id,$index="test",$type="member")
    {
        $result = array();
        if($this->_isconnected)
        {
           $result= $this->elastic_obj->createDocument(json_encode($document), $id, $index, $type);           
        }
        return $result;
    }
    
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
   
   /*return only count of result*/
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
   
   /*return aggregations result,it is kind of group by(Facets)*/
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
    * @param associative array $operations array which contain key as id's and which operation should be perform against that id (like Create, Update and Delete)
    * @param string $index index name on which operation get execute
    * @param string $type type name on which operation get execute
    */
   public function bulk($document,$operations,$index="test",$type="member")
   {
        $result = array();
        if($this->_isconnected)
        {
           $result= $this->elastic_obj->bulk($document, $operations, $index, $type);           
        }
        return $result;
   }
}