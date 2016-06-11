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

use Es\Http\UploadedFileInterface;
use Es\Http\Uploading\AbstractUploadStrategy;
use Es\Http\Uploading\UploadTargetInterface;

class FakeStrategy extends AbstractUploadStrategy
{
    const ERROR = 'foo';

    const OTHER_ERROR = 'coz';

    const ERROR_DESCRIPTION = 'bar';

    const OTHER_ERROR_DESCRIPTION = 'con';

    protected $errors = [
        self::ERROR       => self::ERROR_DESCRIPTION,
        self::OTHER_ERROR => self::OTHER_ERROR_DESCRIPTION,
    ];

    protected $fakeOption = '';

    protected $callback;

    public function setFakeOption($option)
    {
        $this->fakeOption = $option;
    }

    public function getFakeOption()
    {
        return $this->fakeOption;
    }

    public function setCallback(callable $callback = null)
    {
        $this->callback = $callback;
    }

    public function fakeDecideOnSuccess()
    {
        $this->decideOnSuccess();
    }

    public function fakeDecideOnFailure()
    {
        $this->decideOnFailure(self::ERROR);
    }

    public function fakeDecideOnOtherFailure()
    {
        $this->decideOnFailure(self::OTHER_ERROR);
    }

    public function fakeDecideOnFailureWithUnexpectedError()
    {
        $this->decideOnFailure('unspecified_error');
    }

    public function __invoke(UploadedFileInterface $file, UploadTargetInterface $target)
    {
        if ($this->callback) {
            $callback = $this->callback;
            $callback($file, $target);
        }
    }
}
