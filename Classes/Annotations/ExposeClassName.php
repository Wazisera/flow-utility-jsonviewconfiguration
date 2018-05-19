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
final class ExposeClassName extends AbstractAnnotation {

    /**
     * @var boolean
     */
    public $qualifiedName = true;

    /**
     * @param array $values
     */
    public function __construct(array $values)  {
        if (isset($values['value']) || isset($values['qualifiedName'])) {
            $this->qualifiedName = isset($values['qualifiedName']) ? (boolean)$values['qualifiedName'] : (boolean)$values['value'];
        }
    }

}

