Introduction
============
The `Es\Http\Server` provides an opportunity the receipt of the request and
sending  response in a simple manner. It is designed to be implements of two
different architectural approach to work with HTTP messages.

If the architecture of the application involves the use of middleware software, 
the server has a method to obtain an instance of the request and response:
```
$response = $server->getResponse();
return $response->withSatatus(404);
```

Otherwise, the server can be a central repository for the current instance of 
the response or request:
```
$response = $server->getResponse();
$server->setResponse($response->withStatus(404));
```

# Obtaining server instance

## If the package is used standalone

```
$server = new \Es\Server\Server();
```

## If the package is used as component of System

```
$server = $services->get('Server');
```
 
