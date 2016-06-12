Introduction
============
The `Es\Http\Server` provides an opportunity the receipt of the request and
sending  response in a simple manner. It is designed to be implements of two
different architectural approach to work with HTTP messages.

If the architecture of the application involves the use of middleware, the 
server has getters to obtain an instance of the request and response:
```
$response = $server->getResponse();
$response = $response->withSatatus(404);

$request = $server->getRequest();
$request = $request->withAttribute('foo', 'bar');
```

Otherwise, the server can be used as a central repository for the current
instance of the response or request:
```
$response = $server->getResponse();
$server->setResponse($response->withStatus(404));

$request = $server->getRequest();
$server->setRequest($request->withAttribute('foo', 'bar');
```

# Obtaining server instance

## If the package is used standalone

```
$server = new \Es\Http\Server();
```

## If the package is used as component of System

```
$server = $services->get('Server');
```
 
# Sending HTTP response
```
$response = $server->getResponse();
$emitter  = $server->getEmitter();
$emitter->emit($response);
```

# Obtaining the request
```
$request = $server->getRequest();
```

If you do not use the middleware architecture, you can always get the original 
request as follows:
```
$originalRequest = $server->getRequest(false);
```

# Obtaining the response
```
$response = $server->getResponse();
```

To obtain a new instance of the response without any parameters:
```
$newResponse = $server->getResponse(false);
```
