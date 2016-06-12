# Elasticsearch
Elasticsearch client

Elastic search major files: 
Curl.php
Curlresponse.php
Elastic.php
Elasticsearch.php

Curl.php and Curlresponse.php files are in built files which are easily available on Google
.

Elastic.php, this file contains all the major functions which is used to create, update, get and delete documents
. 

Elasticindexer.php, created this file to manage or accept user request data in simple format, for e.g. if developer wants to add new document then he has to pass data in simple array format to createDocument() function, internally this function call Elastic.php function and create proper Json data which we need to create document. For more reference please look test.php file.

Steps to use : 

1. Install latest elastic search version  and start elastic search  service .
2. Run test.php where you will get add document and get document sample functions .

# Examples of elastic search

# Create elastic indexer object

```
include('Elasticindexer.php');
include 'Curl.php';
include 'Elastic.php';
$elastic = new Elasticindexer();

```

# Create Document

$doc = array('firstname'=>'pradip','lastname'=>'humane','empid'=>100); 
$res = $elastic->createDocument($doc,3);

# Check document exits and get Document

if($elastic->isDocumentExists(2)) { 
$result = $elastic->getDocument(2); 
var_dump($result); 
}

# Get multiple documents

$doc['ids'] = array(1,2,3); 
$result = $elastic->getDocuments($doc); 
print_r($result);

# Filter in elastic search

$filter['empid']=100; 
$es_query=getESQuery($filter); 
$result = $elastic->Filter("test",'member','AND',$es_query['filter'],$es_query['query']); 
print_r($result);

function getESQuery($postdata=array(),$operator='') { 
$filter = array(); $query=array(); 
$match=array(); 
$match_must_not=array(); 
if(isset($postdata['empid']) || !empty($postdata['empid'])) { 
   if(is_array($postdata['empid']) && !empty($postdata['empid'])) { 
          foreach($postdata['empid'] as $key) { 
            if($operator == 'not') { 
                $match_must_not[]['match']=array('empid'=>$key); 
                } 
                else { 
                 $match[]['match']=array('empid'=>$key); 
                 } 
        } 
      } 
    else { 
    $match[]['match']=array('empid'=>$postdata['empid']); 
    } 
  } 
  
  if(!empty($match)) { 
   $query['bool']['must'] = $match; 
  } 
  if($operator == 'not' || !empty($match_must_not)) { 
    $query['bool']['must_not'] = $match_must_not; 
  } 
  return array('filter'=>$filter,'query'=>$query); 
}



