<?php
namespace Wazisera\Utility\JsonViewConfiguration\Annotations;

/*                                                                                       *
 * This script belongs to the package "Wazisera.Utility.JsonViewConfiguration".          *
 *                                                                                       *
 *                                                                                       */

abstract class AbstractAnnotation {

    /**
     * @var array
     */
    public $ignoreForVariant = array();

    /**
     * @var array
     */
    public $onlyForVariant = array();

}

