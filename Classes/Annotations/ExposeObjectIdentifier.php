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
final class ExposeObjectIdentifier extends AbstractAnnotation {

    /**
     * @var string
     */
    public $identifierKey;

    /**
     * @param array $values
     */
    public function __construct(array $values)  {
        if (isset($values['value']) || isset($values['identifierKey'])) {
            $this->identifierKey = isset($values['identifierKey']) ? (string)$values['identifierKey'] : (string)$values['value'];
        }
    }

}

