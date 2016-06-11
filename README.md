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




