#####The following is prior to change and does NOT fully reflect current state.

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
~~BPI REST API is based on usage of up to level 3 of Richardson Maturity Model and custom Hypermedia types.~~

~~The key advantages of REST:~~
* REST emphasizes clarity which leads to more understandable systems that are easier to maintain
* REST focusses on document messages with broad applicability instead of service-specific interfaces.
* Systems integration activity is spent where it is of most value: on business domain level documents.
* The Web (over 230 Million Web sites and over 1.7 Billion users) provides excellent protection of any investment in Web Architecture. These technologies won't go out of fashion anytime soon.

### Entry points
~~The API has two main entry points:~~
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
~~The Symfony2 NelmioApiDocBundle bundle provides API auto documentation, located in annotations within the actual code.~~
~~It is very useful to keep documentation in actual state.~~

### Handling media files
~~Before pushing the data client side SHOULD encode images into base64 format and replace HTTP URLs to Data URI.~~
~~Images attached to content should be also converted and defined in `attachments` field in `application/vnd.bpi.node-scheme+json`.~~

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


Versioning policy
--------

See [semver.org][semver.org].

[RFC2616]: http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
[semver.org]: http://semver.org/
