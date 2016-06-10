<?php 
include('Elasticindexer.php');
include 'Curl.php';
include 'Elastic.php';

$elastic = new Elasticindexer();
/*Create document*/
$doc = array('firstname'=>'pradip','lastname'=>'humane','empid'=>100);
$res = $elastic->createDocument($doc,3);
$doc = array('firstname'=>'Chandra','lastname'=>'sir','empid'=>101);
$res = $elastic->createDocument($doc,2);
var_dump($res);

/* get Document by id with is document exists function */
if($elastic->isDocumentExists(2))
{
    $result = $elastic->getDocument(2);
    var_dump($result);
}

/*get multiple Document by id*/
$doc['ids'] = array(1,2,3);
$result = $elastic->getDocuments($doc);
print_r($result);

/* filter with single id */
$filter['empid']=100;
$es_query=getESQuery($filter);
$result = $elastic->Filter("test",'member','AND',$es_query['filter'],$es_query['query']);     
print_r($result);
  

function getESQuery($postdata=array(),$operator='')
    {
        $filter = array();
        $query=array();
        $match=array();
        $match_must_not=array();        
        if(isset($postdata['empid']) || !empty($postdata['empid']))
        {
            if(is_array($postdata['empid']) && !empty($postdata['empid']))
            {
                foreach($postdata['empid'] as $key)
                {
                    if($operator == 'not')
                    {
                        $match_must_not[]['match']=array('empid'=>$key);
                    }
                    else
                    {
                        $match[]['match']=array('empid'=>$key);
                    }
                }
            }
            else
            {
                $match[]['match']=array('empid'=>$postdata['empid']);
            }
        }
        
        
        if(!empty($age) && !empty($toage))
        {
            $filter['range']['age'] = array('gte'=>$age,'lte'=>$toage) ;
        }
        
        
        if(isset($postdata['diet']) && !empty($postdata['diet']))
        {
            if(is_array($postdata['diet']) && count($postdata['diet']) > 1)
            {
                foreach($postdata['diet'] as $diet_v)
                {
                    $filter['or'][]['term']= array('eating_habbits_id'=>$diet_v) ;
                }
            }
            
        }
        if(!empty($match))
        {
            $query['bool']['must'] = $match;
        }
        if($operator == 'not' || !empty($match_must_not))
        {
            $query['bool']['must_not'] = $match_must_not;
        }
        //pr($postdata);
        //pr($filter);
        //pr($query);
        return array('filter'=>$filter,'query'=>$query);
    }            
?>
