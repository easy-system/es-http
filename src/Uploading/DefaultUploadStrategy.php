<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Uploading;

/**
 * The default upload strategy.
 */
class DefaultUploadStrategy extends StrategiesQueue
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->attach(new DirectoryStrategy(), 200);
        $this->attach(new MoveStrategy(),      100);
    }
}
