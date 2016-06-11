<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Test\Uploading;

use Es\Http\UploadedFile;
use Es\Http\Uploading\StrategiesQueue;
use Es\Http\Uploading\UploadOptions;
use Es\Http\Uploading\UploadTarget;
use ReflectionProperty;

require_once 'FakeStrategy.php';

class StrategiesQueueTest extends \PHPUnit_Framework_TestCase
{
    public function testAttachAttachesStrategyWithPriority()
    {
        $queue    = new StrategiesQueue();
        $strategy = new FakeStrategy();
        $queue->attach($strategy, 100);
        $this->assertEquals([100 => $strategy], $queue->getArrayCopy());
    }

    public function testAttachResetSortedQueue()
    {
        $queue  = new StrategiesQueue();
        $target = new UploadTarget('foo');
        $strategy = new FakeStrategy();
        $queue->attach($strategy, 100);
        $queue(new UploadedFile(), $target);

        $reflection = new ReflectionProperty($queue, 'sortedQueue');
        $reflection->setAccessible(true);
        $this->assertSame([100 => $strategy], $reflection->getValue($queue));

        $queue->attach(new FakeStrategy(), 200);
        $this->assertNull($reflection->getValue($queue));
    }

    public function testAttachRaiseExceptionIfStrategyWithSpecifiedPriorityAlreadyExists()
    {
        $queue  = new StrategiesQueue();
        $queue->attach(new FakeStrategy(), 100);
        $this->setExpectedException('InvalidArgumentException');
        $queue->attach(new FakeStrategy(), 100);
    }

    public function testAttachRaiseExceptionIfAttachDuringInvoked()
    {
        $queue    = new StrategiesQueue();
        $target   = new UploadTarget('foo');
        $strategy = new FakeStrategy();
        $queue->attach($strategy, 100);

        $strategy->setCallback(function () use ($queue) {
            $queue->attach(new FakeStrategy(), 1000);
        });

        $this->setExpectedException('RuntimeException');
        $queue(new UploadedFile(), $target);
    }

    public function testAttachSetOptionToAttachedStrategy()
    {
        $queue  = new StrategiesQueue();
        $options = new UploadOptions(['foo' => 'bar']);
        $queue->setOptions($options);
        $strategy = new FakeStrategy();
        $queue->attach($strategy, 100);
        $this->assertSame($options, $strategy->getOptions());
    }

    public function testDetachDetachesStrategy()
    {
        $queue    = new StrategiesQueue();
        $strategy = new FakeStrategy();
        $queue->attach($strategy, 100);

        $queue->detach(100);
        $this->assertEquals([], $queue->getArrayCopy());
    }

    public function testDetachResetSortedQueue()
    {
        $queue    = new StrategiesQueue();
        $strategy = new FakeStrategy();
        $queue->attach($strategy, 100);
        $target = new UploadTarget('foo');

        $queue(new UploadedFile(), $target);
        $reflection = new ReflectionProperty($queue, 'sortedQueue');
        $reflection->setAccessible(true);
        $this->assertSame([100 => $strategy], $reflection->getValue($queue));

        $queue->detach(100);
        $this->assertNull($reflection->getValue($queue));
    }

    public function testDetachRaiseExceptionWhenDetachDuringInvoked()
    {
        $queue    = new StrategiesQueue();
        $target   = new UploadTarget('foo');
        $strategy = new FakeStrategy();
        $queue->attach($strategy, 100);

        $strategy->setCallback(function () use ($queue) {
            $queue->detach(100);
        });

        $this->setExpectedException('RuntimeException');
        $queue(new UploadedFile(), $target);
    }

    public function testGetArrayCopyReturnsArrayCopy()
    {
        $queue = new StrategiesQueue();
        $this->assertSame([], $queue->getArrayCopy());

        $first  = new FakeStrategy();
        $second = new FakeStrategy();
        $third  = new FakeStrategy();
        $queue
            ->attach($first,  300)
            ->attach($second, 200)
            ->attach($third,  100);

        $expected = [
            300 => $first,
            200 => $second,
            100 => $third,
        ];
        $this->assertSame($expected, $queue->getArrayCopy());
    }

    public function testSetOptionsSetsOptions()
    {
        $options = new UploadOptions(['foo' => 'bar']);
        $queue   = new StrategiesQueue();
        $queue->setOptions($options);
        $this->assertSame($options, $queue->getOptions());
    }

    public function testSetOptionsSetsOptionsToStrategiesInQueue()
    {
        $queue  = new StrategiesQueue();

        $options = new UploadOptions(['foo' => 'bar']);

        $first  = new FakeStrategy();
        $second = new FakeStrategy();
        $third  = new FakeStrategy();

        $queue
            ->attach($first,  300)
            ->attach($second, 200)
            ->attach($third,  100);

        $queue->setOptions($options);

        $this->assertSame($options, $first->getOptions());
        $this->assertSame($options, $second->getOptions());
        $this->assertSame($options, $third->getOptions());
    }

    public function testGetStateReturnsNullIfQueueEmpty()
    {
        $queue = new StrategiesQueue();
        $this->assertNull($queue->getState());
    }

    public function testGetOperationErrorReturnsNullIfQueueEmpty()
    {
        $queue = new StrategiesQueue();
        $this->assertNull($queue->getOperationError());
    }

    public function testGetOperationErrorDescriptionReturnsNullIfQueueEmpty()
    {
        $queue = new StrategiesQueue();
        $this->assertNull($queue->getOperationErrorDescription());
    }

    public function testGetStateReturnsStateOfLastInvokedStrategy()
    {
        $queue  = new StrategiesQueue();
        $target = new UploadTarget('foo');

        $first  = new FakeStrategy();
        $second = new FakeStrategy();
        $third  = new FakeStrategy();

        /*
         * state of $first  = STATE_SUCCESS
         * state of $second = STATE_BREAK | STATE_FAILURE
         * state of $third  = null by default
         */
        $first->fakeDecideOnSuccess();
        $second->fakeDecideOnFailure();

        $queue
            ->attach($first,  300)
            ->attach($second, 200)
            ->attach($third,  100);

        // expect return state of $second, because it decides on error
        $queue(new UploadedFile(), $target);
        $this->assertSame($second::STATE_BREAK | $second::STATE_FAILURE, $queue->getState());

        // expect return state of $third, because queue is fully executed
        $second->fakeDecideOnSuccess();
        $queue(new UploadedFile(), $target);
        $this->assertNull($queue->getState());
    }

    public function testGetOperationErrorReturnsErrorOfStrategyThatDecidesOnError()
    {
        $queue  = new StrategiesQueue();
        $target = new UploadTarget('foo');

        $first  = new FakeStrategy();
        $second = new FakeStrategy();
        $third  = new FakeStrategy();

        $second->fakeDecideOnOtherFailure();

        $queue
            ->attach($first,  300)
            ->attach($second, 200)
            ->attach($third,  100);

        $queue(new UploadedFile(), $target);
        $this->assertSame($queue->getOperationError(), $second::OTHER_ERROR);
    }

    public function testHasOperationErrorIfNoOperations()
    {
        $queue  = new StrategiesQueue();
        $this->assertFalse($queue->hasOperationError());
    }

    public function testHasOperationErrorIfNoError()
    {
        $queue  = new StrategiesQueue();
        $target = new UploadTarget('foo');

        $first  = new FakeStrategy();
        $second = new FakeStrategy();
        $third  = new FakeStrategy();

        $third->fakeDecideOnSuccess();

        $queue
            ->attach($first,  300)
            ->attach($second, 200)
            ->attach($third,  100);

        $queue(new UploadedFile(), $target);
        $this->assertFalse($queue->hasOperationError());
    }

    public function testHasOperationErrorOnFailure()
    {
        $queue  = new StrategiesQueue();
        $target = new UploadTarget('foo');

        $first  = new FakeStrategy();
        $second = new FakeStrategy();
        $third  = new FakeStrategy();

        $second->fakeDecideOnFailure();

        $queue
            ->attach($first,  300)
            ->attach($second, 200)
            ->attach($third,  100);

        $queue(new UploadedFile(), $target);
        $this->assertTrue($queue->hasOperationError());
    }

    public function testGetOperationErrorDescriptionReturnsErrorDescriptionOfStrategyThatDecidesOnError()
    {
        $queue  = new StrategiesQueue();
        $target = new UploadTarget('foo');

        $first  = new FakeStrategy();
        $second = new FakeStrategy();
        $third  = new FakeStrategy();

        $second->fakeDecideOnOtherFailure();

        $queue
            ->attach($first,  300)
            ->attach($second, 200)
            ->attach($third,  100);

        $queue(new UploadedFile(), $target);
        $this->assertSame($queue->getOperationErrorDescription(), $second::OTHER_ERROR_DESCRIPTION);
    }
}
