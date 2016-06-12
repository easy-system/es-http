Introduction
============
The class `Es\Http\ServerRequest` represents the incoming server-side HTTP request.
It implements the `Psr\Http\Message\ServerRequestInterface` interface.

The class `Es\Http\ServerRequest` provides additional methods that are not 
defined at this time in the `Psr\Http\Message\ServerRequestInterface` interface:

- `withAttributes(array $attributed)` - return an instance of `ServerRequest` 
  with the specified attributes
- `withAddedAttributes(array $attributes)` - return an instance  of 
  `ServerRequest` with the specified attributes appended with the existed
  attributes

# Usage

To get an instance of `Es\Http\ServerRequest` use the `Es\Http\Server`:
```
$request = $server->getRequest();
```
