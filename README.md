BPI Web-service
========
This is a web-service, with numerous methods, which makes it possible to use BPI.
The web-service is queried and returns lists, nodes, etc. as it is the user interface’s task to present the content and other data in the database.

The following functionality is included:
* Create, edit and delete nodes
* Provide a list of nodes and it’s content (based on the parameters sent / search criteria)

Overview
--------
The REST architecture style provides ability to build easy to use, organic to internet ecosystem and scalable web services.
BPI REST API is based on usage of up to level 3 of Richardson Maturity Model and custom Hypermedia types.

The key advantages of REST:
* REST emphasizes clarity which leads to more understandable systems that are easier to maintain
* REST focusses on document messages with broad applicability instead of service-specific interfaces.
* Systems integration activity is spent where it is of most value: on business domain level documents.
* The Web (over 230 Million Web sites and over 1.7 Billion users) provides excellent protection of any investment in Web Architecture. These technologies won't go out of fashion anytime soon.

### Entry points
The API has two main entry points:
*   `GET /node/list.json`
*   `GET /node/scheme.json`

All other URIs will be discovered while processing "links" section of media types.
This will decouple client code from exact URIs, and gives possibility to change URIs without compatibility breaks.

### Content negotiation
Client SHOULD negotiate the type of content by sending preferred HTTP content type header.
Server SHOULD provide the content type of response.

Available BPI media types:
*    `application/vnd.bpi.api+xml`

Version section in media type gives graceful ability to evolve web service in the future.

### Documentation
The Symfony2 NelmioApiDocBundle bundle provides API auto documentation, located in annotations within the actual code.
It is very useful to keep documentation in actual state.

### Handling media files
Before pushing the data client side SHOULD encode images into base64 format and replace HTTP URLs to Data URI.
Images attached to content should be also converted and defined in `attachments` field in `application/vnd.bpi.node-scheme+json`.

The BPI’s response goes with HTTP URLs of media relative to BPI, so client MAY cache them, or replace with local URLs.

### Authorization
Authorization is based on Public Key concept.
Both sides has Private key, Public key and Hash function.
Client generates Token using Hashing function of Public key and Private key.
Then each request MUST contain Public key and Token.
Server generates own Token based on incoming Public key and local Private key.
Client considered as authorised If tokens match.

### Status codes
BPI API status codes are based on [RFC2616 part 10][RFC2616] of Hypertext Transfer Protocol HTTP/1.1 specification. 

Error handling

Errors are returned using standard HTTP error code syntax.
Any additional info is included in the body of the return call. 

Standard API errors
*   400    Bad input parameter. Error message should indicate which one and why.
*   401    Bad or expired access token. 
*   404    File or folder not found at the specified path.
*   405    Request method not expected (generally should be GET or POST).
*   5xx    Server error. Contact system administrator.


Database Schema
--------
    Node = {
        "_id" : "<$id>",   	                // BPI node ID
        "agency": {			                    // reference to library
            "$ref": "Agency",
            "$id": "<$id>"
        },
        "resource": {			                  // syndicated item
            "id": "<number>",               // item ID in local system (Drupal, Joomla)
            "ctime": "<date>",		          // creation time in local system
            "title": "<string>",	          // title of item
            "body": "<string>",		          // contents of item 
            "author": "<string>",	          // author 
            "user_id": "<number>",	        // user ID in local system
            "teaser": "<string>",	          // short description of item
            "relations": [		              // item relations with different objects
                { 
                    rel: "<string",	        // material, agency, etc
                    reference: "<string>" 	// reference number
                }
                ],
            "attachments": [		            // list of attached or embedded in content media files
                {
                    "_ref_id": "GridFS.ObjectId()"
                }
            ],
        },
        "status": "<string>",		            // node status: pending/published/failed
            "category": {                   // BPI category
            "name": "<string>",
            "_ref_id": "Category.$id"
        },
        "audience": {			                  // BPI audience
            "name": "<string>",
            "_ref_id": "Audience.$id"
        },
        "ctime": "<date>",		              // creation time
        "mtime": "<date>",		              // modification time
        "comment": "<string>",	          	// comment while publishing 
        "path": "<string>",		              // materialized path to store revisions of node
        "type": "<string>",		              // BPI node type: article, event, etc
    }

    Agency = {				                      // library
        "id": "<number>",		                // 6 digits library id
        "name": "<string>",		              // library name
        "moderator": "<string>",	          // library moderator, a person responsible for communications
        "moderator_email": "<string>",      // moderator email
        "private_key": "<string>",	        // private key used for authentication
        "shared_key": "<string>",	          // shared key used for authentication
    }

    Log = {				                          // usage log
        "ctime": "<date>",		              // creation time
        "agency_id": "<number>",	          // library ID
        "node_id": "<number>",		          // BPI node ID
        "action": "<string>"		            // action taken in BPI: push node, fetch node, etc
    }

    Audience = {				                    // available audiences
        "name": "<string>"
    }

    Category = {				                    // available categories
        "name": "<string>"
    }

    /* FOSUserBundle */
    User = {				                        // admin panel users
        "_id": "<$id>",			
        "email": "<string>",		
        "enabled": "<boolean>",
        "lastLogin": "<date>",
        "password": "<string>",
        "username": "<string>"
    }

    /* GridFS */
    Files = {				                        // media files stored on BPI
        "_id" : "<number>",
        "length" : "<number>",
        "chunkSize" : "<number>",
        "uploadDate" : "<date>",
        "md5" : "<string>"
    }

    /* GridFS */
    Chunks = {				                      // media files stored on BPI
        "_id" : "<number>",
        "files_id" : "<number>",
        "n" : "<number>",
        "data" : "<binary>",
    }


[RFC2616]: http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html