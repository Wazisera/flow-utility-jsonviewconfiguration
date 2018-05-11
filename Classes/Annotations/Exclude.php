<?php
namespace Wazisera\Utility\JsonViewConfiguration\Annotations;

/*                                                                                       *
 * This script belongs to the package "Wazisera.Utility.JsonViewConfiguration".          *
 *                                                                                       *
 *                                                                                       */

use Doctrine\Common\Annotations\Annotation as DoctrineAnnotation;

/**
 * @Annotation
 * @DoctrineAnnotation\Target({"CLASS", "PROPERTY"})
 */
final class Exclude {

    /**
     * @var array
     */
    public $properties;

    /**
     * @param array $values
     */
    public function __construct(array $values)  {
        if (isset($values['value']) || isset($values['properties'])) {
            $this->properties = isset($values['properties']) ? (array)$values['properties'] : (array)$values['value'];
        }
    }

}

