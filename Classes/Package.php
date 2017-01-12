<?php
namespace Flownative\Fluid\Tideways;

/*
 * This file is part of the Flownative.Fluid.Tideways package.
 *
 * (c) Flownative GmbH - https://www.flownative.com/
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Tideways\Profiler;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The Flownative.Fluid.Tideways Package class adds additional instrumentation calls.
 */
class Package extends BasePackage
{
    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        if (!class_exists(Profiler::class)) {
            return;
        }

        Profiler::watchCallback(
            'TYPO3\Fluid\ViewHelpers\RenderViewHelper_Original::render',
            function ($context) {
                $span = Profiler::createSpan('fluid-viewhelper');

                // 0 = $section, 1 = $partial
                $toRender = isset($context['args'][1]) ? 'partial:' . $context['args'][1] : 'section:' . $context['args'][0];
                $span->annotate(['title' => 'Render(' . $toRender . ')']);

                return $span->getId();
            }
        );
    }
}
