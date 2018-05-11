<?php
namespace Wazisera\Utility\JsonViewConfiguration;

/*                                                                                       *
 * This script belongs to the package "Wazisera.Utility.JsonViewConfiguration".          *
 *                                                                                       *
 *                                                                                       */

use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;

/**
 */
class Package extends BasePackage {

    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(Bootstrap $bootstrap) {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();

        $dispatcher->connect(Bootstrap::class, 'finishedCompiletimeRun', function () use ($bootstrap, $dispatcher) {
            // clean cache
        });
    }
}
