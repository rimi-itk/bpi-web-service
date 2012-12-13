BPI API Test Interface Introduction
===================================

Test interface aims two goals:

- Give interface to make real requests and recieve real responses from the API
- Provide self-descriptive documentation for each resource

REST API Endpoints
------------------

BPI API has limited set of resources originally known by client.

* [List of nodes](/) `/`
* [Profile dictionary](/profile_dictionary.html) `/profile_dictionary`

> Limitation of originally known URLs aims to minimize tight coupling of client with particular URL of the resource. Instead client must understand [hypermedia concept](media_type.html#Links) and follow links considering relation names.

Endpoins are shown in top navigation of test interface.

Self-descriptive Documentation
------------------------------
Each resource (web page) has different representations. Representation defined by file extension. 

> `http://bpi-api.example.com/resource.bpi`

Common ones are:

* **bpi**
  BPI API custom Media Type (application/vnd.bpi.api+xml) is preffered to use.

* **xml**
  Fallback for clients who doesn't familiar with `bpi` Media Type. The difference that client requesting `bpi` is explicitly intended to use this format considering its documentation. Usage of `xml` means that client understands XML syntax and can process it, but doesn't know about BPI Media Type.

* **html**
  Self-descriptive representation of resource for humans. *This is the test interface for particular resource*. Each resource has own test interface.

* **json**
  Intended to use by clients familiar with json syntax.


Main tabs
---------

HTTP methods are the common way of use of resource. 

* **GET**
  Used for retrieving resources.

* **POST**
  Used for creating resources, or performing custom actions (such as quering node list).

* **OPTIONS**
  Used for discover HTTP methods applicable to resource. Mainly used by self-descriptive documentation.

* **PUT**
  Used for (re)placing resources when location is known by client. Used to upload media assets.

See [RFC2616-9 Method Definitions](http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html).

Input section
-------------

Send button performs request to API. If resource requires request body, editor will be shown. That requirement indicated in response of `OPTIONS` request to the resource.

### Authentication Section

Defines on behalf of witch agency request is performed. BPI uses Public Key authentification. Each agency has own Public Key and Secret. Selecting the agency will select apropriate PK.

### Input editor

Editor has buttons on top. Each button is an entity sample. For example if some resourse expects *node* entity, user can insert sample of that entity and than change its data and structure. Some resources expects many entities in request, so many entity samples can be inserted.

> Each entity sample is valid bpi document. Insertion of many entities will produce invalid structure, so it must be modified: remove XML processing instruction and root `<bpi>` tag.

Output section
--------------

HTTP Status Code is shown after request was performed. This status code will be displayed even request was produced an error on API side. See [RFC2616-10 Status Code Definitions](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html).

If request was not sent error message will be shown. This means that request not reached the server and there is no any status code for that.

### Adopted output tab

Adopted (for humans) output is transformed from raw output to focus on data rather that structure. Other goal is to provide clickable links to follow other resources. See [Hypermedia section of BPI Media Type](media_type.html#Links).

### Raw output tab

Displays output as is without any modifications.

Expected Entities
-----------------

Both input and output may expect entity. See [BPI Custom Media Type](media_type.html#Entities) for explanation.

