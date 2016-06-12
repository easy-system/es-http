The basic usage
===============

Use the `Es\Http\ServerRequest` to get an uploaded files:
```
$request = $server->getRequest();
$files   = $request->getUploadedFiles();
```
The above example will return an array of instances of `Es\Http\UploadedFile`.

A simple example of basic usage of the array with uploaded files:
```
$request = $server->getRequest();
$files   = $request->getUploadedFiles();

if (! isset($files['foo']) {
    return;
}

$targetDir = '/path/to/user/directory';
$options = [
    'target_directory' => $targetDir,
];

foreach ($files['foo'] as $uploadedFile) {
    if ($uploadedFile->getError()) {
        continue;
    }
    // Using the name of file, that was specified by the client, is unsafe
    $target   = $uploadedFile->getClientFileName();
    $strategy = $uploadedFile->moveTo($target, $options);
    if ($strategy->hasOperationError()) {
        var_dump($strategy->getOperationError());
        var_dump($strategy->getOperationErrorDescription());
    }
}
```
This behavior provided by default. To change or extend this behavior, see the 
uploading strategies.

# The upload strategies

The class `Es\Http\UploadedFile` contains two additional methods that are not 
defined at this time in the `Psr\Http\Message\UploadedFileInterface` interface:

- `setUploadStrategy()`
- `getUploadStrategy()`

These two methods allow you to change the upload behavior as you wish.
When you call the method `moveTo($target)`, it uses the specified  upload strategy.
If the strategy has not been specified, it uses the strategy by default 
`Es\Http\Uploading\DefaultUploadStrategy`.

To specify strategy for uploaded files:
```
$myStrategy = new \My\Uploading\Strategy();

foreach ($files['foo'] as $uploadedFile) {
    if ($uploadedFile->getError()) {
        continue;
    }
    $uploadedFile->setUploadStrategy($myStrategy);
    $uploadedFile->moveTo($target);

    if ($myStrategy->hasOperationError()) {
        var_dump($myStrategy->getOperationError());
        var_dump($myStrategy->getOperationErrorDescription());
    }
}
```