Installation
============

`$ docker run -d -p {port}:80 pedrotroller/http-markup`

Usage
=====

There is only one end point to this api matching on `POST /`

You just have to post your markdown inside the body of your request and to add to set the Content-Type. For the moment, only `text/markdown` is supported.

Example
=======

For example, the request

```txt
POST http://localhost/

Content-Type=text/markdown

#The title#

#The subtitle#
```

will return

```html
<h1>The title</h1>

<h2>The subtitle</h2>
```
