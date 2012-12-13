BPI Custom Media Type Overview
==============================

BPI Custom Media Type is expressed in XML syntax. Valid document should have the root node:

`<bpi version=""></bpi>`

Root node may have one or many **entities**. If there are many first level entities they must be of the same name.

Entities
--------

Entity is predefined set of **links**, **properties** and **sub entities**.

`<entity name=""></entity>`

Links
-----

BPI Media Type may have one or more links following to other resources. These are meant to provide explicit URLs so that proper API clients don’t need to construct URLs on their own. It is highly recommended that API clients use these. Doing so will make future upgrades of the API easier for developers.

`<links><link rel="relation_name" href="URI"></links>`

Properties
----------

Propery is a holder of primitive data with some meta information.

`<properties><property name="" type="" title="">value</property></properties>`


Sub entities
------------

Sub entity is the same as first class entity except that it always related with the parent entity and can't exists separately.
