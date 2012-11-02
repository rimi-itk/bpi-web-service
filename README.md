BPI Web-service
========
This is a web-service, with numerous methods, which makes it possible to use BPI.
The web-service is queried and returns lists, nodes, etc. as it is the user interface’s task to present the content and other data in the database.

The following functionality is included:
* Create, edit and delete nodes
* Provide a list of nodes and it’s content (based on the parameters sent / search criteria)


--------


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