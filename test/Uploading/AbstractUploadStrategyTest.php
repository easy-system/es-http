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

use Es\Http\Uploading\UploadOptions;

require_once 'FakeStrategy.php';

class AbstractUploadStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testSetOptionsFindAndUseSettersToSetSpecifiedOption()
    {
        $options  = new UploadOptions(['fake_option' => 'foo']);
        $strategy = new FakeStrategy();
        $strategy->setOptions($options);
        $this->assertEquals('foo', $strategy->getFakeOption());
    }

    public function testGetStateReturnsNullBeforeOperationCall()
    {
        $strategy = new FakeStrategy();
        $this->assertNull($strategy->getState());
    }

    public function testHasOperationError()
    {
        $strategy = new FakeStrategy();
        $this->assertFalse($strategy->hasOperationError());

        $strategy->fakeDecideOnFailure();
        $this->assertTrue($strategy->hasOperationError());

        $strategy->fakeDecideOnSuccess();
        $this->assertFalse($strategy->hasOperationError());
    }

    public function testGetOperationErrorReturnsNullIfNoErrors()
    {
        $strategy = new FakeStrategy();
        $this->assertNull($strategy->getOperationError());
    }

    public function testGetOperationErrorDescriptionReturnsNullIfNoErrors()
    {
        $strategy = new FakeStrategy();
        $this->assertNull($strategy->getOperationErrorDescription());
    }

    public function testDecideOnFailureSetStateToFailure()
    {
        $strategy = new FakeStrategy();
        $strategy->fakeDecideOnFailure();
        $this->assertTrue((bool) ($strategy::STATE_FAILURE & $strategy->getState()));
    }

    public function testDecideOnFailureSetStateToBreak()
    {
        $strategy = new FakeStrategy();
        $strategy->fakeDecideOnFailure();
        $this->assertTrue((bool) ($strategy::STATE_BREAK & $strategy->getState()));
    }

    public function testDecideOnFailureSetOperationError()
    {
        $strategy = new FakeStrategy();
        $strategy->fakeDecideOnFailure();
        $this->assertEquals($strategy::ERROR, $strategy->getOperationError());
    }

    public function testDecideOnFailureSetOperationErrorDescription()
    {
        $strategy = new FakeStrategy();
        $strategy->fakeDecideOnFailure();
        $this->assertEquals($strategy::ERROR_DESCRIPTION, $strategy->getOperationErrorDescription());
    }

    public function testDecideOnFailureWithUnexpectedErrorRaiseException()
    {
        $strategy = new FakeStrategy();
        $this->setExpectedException('UnexpectedValueException');
        $strategy->fakeDecideOnFailureWithUnexpectedError();
    }

    public function testDecideOnSuccesSetStateToSuccess()
    {
        $strategy = new FakeStrategy();
        $strategy->fakeDecideOnSuccess();
        $this->assertFalse((bool) ($strategy::STATE_FAILURE & $strategy->getState()));
    }

    public function testDecideOnSuccessCleansOperationError()
    {
        $strategy = new FakeStrategy();

        $strategy->fakeDecideOnFailure();
        $this->assertEquals($strategy::ERROR, $strategy->getOperationError());

        $strategy->fakeDecideOnSuccess();
        $this->assertNull($strategy->getOperationError());
    }

    public function testDecideOnSuccessCleansOperationErrorDescription()
    {
        $strategy = new FakeStrategy();

        $strategy->fakeDecideOnFailure();
        $this->assertEquals($strategy::ERROR_DESCRIPTION, $strategy->getOperationErrorDescription());

        $strategy->fakeDecideOnSuccess();
        $this->assertNull($strategy->getOperationErrorDescription());
    }
}
