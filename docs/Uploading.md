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

# The default upload strategy

The class `Es\Http\Uploading\DefaultUploadStrategy` extends the strategies queue
class and adds from constructor two strategies:

- `Es\Http\Uploading\DirectoryStrategy` - creates specified directory, if it 
  not exists
- `Es\Http\Uploading\MoveStrategy` - moves uploaded file to specified directory

Since the class extends the strategies queue, you can add to this queue their 
own strategies:
```
$defaultStrategy = new \Es\Http\Uploading\DefaultUploadStrategy();
$myStrategy      = new \My\Uploading\Strategy();

$defaultStrategy->attach($myStrategy, 300);

foreach ($files['foo'] as $uploadedFile) {
    if ($uploadedFile->getError()) {
        continue;
    }
    $uploadedFile->setUploadStrategy($defaultStrategy);
    $uploadedFile->moveTo($target);

    if ($defaultStrategy->hasOperationError()) {
        var_dump($defaultStrategy->getOperationError());
        var_dump($defaultStrategy->getOperationErrorDescription());
    }
}
```

# The strategies queue

Ultimately you can create completely your own queue of strategies:
```
class MyUploadStrategyFactory
{
    public static function make()
    {
        $strategiesQueue  = new \Es\Http\Uploading\StrategiesQueue();
        $myFirstStrategy  = new \My\Uploading\FirstStrategy();
        $mySecondStrategy = new \My\Uploading\SecondStrategy();

        $strategiesQueue->attach($myFirstStrategy,  200);
        $strategiesQueue->attach($mySecondStrategy, 100);

        return $strategiesQueue;
    }
}

$strategy = MyUploadStrategyFactory::make();

foreach ($files['foo'] as $uploadedFile) {
    if ($uploadedFile->getError()) {
        continue;
    }
    $uploadedFile->setUploadStrategy($strategy);
    $uploadedFile->moveTo($target);

    if ($strategy->hasOperationError()) {
        var_dump($strategy->getOperationError());
        var_dump($strategy->getOperationErrorDescription());
    }
}
```
