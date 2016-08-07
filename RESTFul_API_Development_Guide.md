# Goal

Imaging we will build a ticket service system, and will provide some RESTFul style APIs like below:
* Create a new ```ticket``` - ```POST http://api.wobase.net/ticket```
* Modify basic infomation of a ```ticket``` - ```PUT http://api.wobase.net/ticket/:ticketId```
* Delete a ```ticket``` - ```DELETE http://api.wobase.net/ticet/:ticketId```
* Get Details of a ```ticket``` - ```GET http://api.wobase.net/ticket/:ticketId```
* Search ```tickets``` by type  - ```GET http://api.wobase.net/ticket```?type=defect|task|enhancement
* Add a new ```comment``` for a ```ticket``` - ```POST http://api.wobase.net/ticket/:ticketId/comment```
* Modify a ```comment``` - ```PUT http://api.wobase.net/ticket/:ticketId/comment/:commentId```
* Delete a ```comment``` - ```DELETE http://api.wobase.net/ticket/:ticketId/comment/:commentId```
* and more...

# Simple knowledge of design RESTFul API

As we known, RESTFul is a web servie style, it use less URLs to provide more functionalities around resources. It likes the CURD for operating tables of RDBMS. So RESTFul Web Services always following the rules as below:

1. The URL must be pointed to an unique resouce (or a single resouce type)
2. A subresource could be identified after its parent resouce in same URL, so that the URL shows the complete relationship between resources in the same service.
3. The HTTP Methods means:
 * GET - ```Search``` (with GET queries) or ```Get Details``` of a resource which include resource ID in the request URL
 * POST - ```Create``` new instance of a resource type 
 * PUT - ```Update``` an existing resource
 * DELETE - ```Delete``` an existing resource

# How to define RESTFul WS API in PWorks?
## Translate URL

In PWorks, the basic unit are actions, each of them can be used to provide individual web service interface with an unique url. A standard raw http request of pworks likes below:

~~~
http://pworks.wobase.net/index.php?actionId=ticket.Search&type=defect
~~~

A static URL could be provided for above request by URL Rewirte, it may like:

~~~
http://pworks.wobase.net/api/ticket.Search?type=defect
~~~

the related rewrite rule may like:

~~~ini
RewriteRule ^/api/(.*)$ index\.php?actionId=$1 [QSA,L]
~~~

For providing same web service interface, RESTFul can make a shorter URL like:

~~~
http://pworks.wobase.net/rest/ticket?type=defect
~~~

**But**, the ```GET``` method **MUST** be forcedly used to request the RESTFul URL, otherwise the real request target cannot be reached. So the related rewrite rule will be  changed as:

~~~ini
RewriteRule ^/rest(/.*)$ index\.php?actionId=rest&url=$1&method=%{REQUEST_METHOD} [QSA,L]
~~~

## Configuration

For the 1st and 2nd URL, it just need define a simple action node in the pworks.xml, likes:

~~~xml
<?xml version="1.0" encoding="UTF-8"?>
<!-- 
All path settings are related to the path of the file index.php
-->
<application id="ticket" default-action="404">
    <filter id="data-validation-inject"  type="global"
        class="pworks.mvc.filter.CachedHttpInputFilter">
        <!-- list action ids will be excluded here
        <exclude id="home"/>
        <exclude id="login"/>
        -->
    </filter>
    <resultTypes>
        <resultType id="php" class="pworks.mvc.result.PhpResult"/>
        <resultType id="json" class="pworks.mvc.result.JsonResult"/>
    </resultTypes>
    <actions>
        <action id="http404">
            <result id="succ" type="php" src="page/404.tpl.php"/>
        </action>
        
        <action id="ticket.Search">
            <result id="succ" type="json"/>
        </action>
    </actions>
</application>
~~~

But, for the RESTFul style WS interface, there must provide capabilities for customizing URL and HTTP Request Method, so the config content are changed as below:

~~~xml
<?xml version="1.0" encoding="UTF-8"?>
<application id="ticket" default-action="rest">
    <filter id="data-validation-inject"  type="global"
        class="pworks.mvc.filter.CachedHttpInputFilter">
        <!-- list action ids will be excluded here
        <exclude id="home"/>
        <exclude id="login"/>
        -->
    </filter>
    <resultTypes>
        <resultType id="json" class="pworks.mvc.result.JsonResult"/>
    </resultTypes>
    <actions>
        <action id="rest" class="pworks.mvc.action.RestRouterAction">
            <result id="succ" type="json"/>
        </action>
        
        <action url="/ticket" method="get" id="ticket.Search"/>
    </actions>
</application>
~~~

The major difference between the two configurations appeared in ```<action>``` node

~~~diff
---        <action id="ticket.Search">
---            <result id="succ" type="json"/>
---        </action>
+++        <action url="/ticket" method="get" id="ticket.Search"/>
~~~

There are two new attributes, that been added since version 1.1, for defining RESTFul API, they are:

- ```url``` - this attribute is used to define the restful-url, e.g.:
 - ``` /ticket ```
 - ``` /ticket/:ticketId ``` 
 - ``` /ticket/:ticketId/comment ``` 
 - ``` /ticket/:ticketId/comment/:commendId ``` 
- ```method``` - this attribute is used to define the HTTP Request Method, in version 1.1, only ```get```, ```post```, ```put```, and ```delete``` can be accepted.


# Implement the API

Once the definitions of the API had been completed, there just left one simple step to do before staring implementation job, it's to create the action classes.

## Include Path Convention

Watch the action config ```<action url="/ticket" method="get" id="ticket.Search"/>```, there is no ```class``` attribute, that's not a defect, PWorks will automatically load this action's class ```SearchAction``` by  ``` include_once('action/ticket/SearchAction.class.php'); ``` statement. The expected files structure for the action's config is simimlar to:

~~~sh
\${htodc}
|--\ index.php
|--\ pworks.xml
|--\ action
   |--\ ticket
      |--\ SearchAction.class.php       
~~~

## Action Class

The skeleton codes of ```SearchAction``` class like below:

```php
<?php
require_once('pworks/mvc/action/BaseAction.class.php');

class SearchAction extends BaseAction{
    // the "type" perporty member will be injected by the filter
    // "data-validation-inject"
    public $type;
    
    public function execute(){
        // Fill the implement codes here ...
        
        
    
    
    
        //--------------------
        // the response json like:
        // {
        //    head:{
        //      status:true|false,
        //      errors:{
        //          $errorCode:$errorMessage,
        //          ... ...
        //      } 
        //    },
        //    body:{
        //      $key:$value,
        //      ... ...
        //    } 
        // }
        //
        // the error messages will be filled in the head.errors part 
        $this->addError($errorCode, $errorMessage);
        
        // the data will be filled in the body part in the response json
        $this->setData($key, $value);
        return 'succ';
    }
}
```

The ```type``` perporty will be autofill be the framework, so the SearchAction class is   protocol-independence, it could be tested as normal class.

The ```$this->addError()``` and ```$this->setData()``` methods store the error messages and results in the action's ```errors``` and ```data``` members, and could be retrieved by ```BaseAction::getErrors()```, and ```BaseAction::getData()``` methods in view component.

~~~ end ~~~