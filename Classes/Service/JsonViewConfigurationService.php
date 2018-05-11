<?php
namespace Wazisera\Utility\JsonViewConfiguration\Service;

/*                                                                                       *
 * This script belongs to the package "Wazisera.Utility.JsonViewConfiguration".          *
 *                                                                                       *
 *                                                                                       */

use Doctrine\Common\Collections\Collection;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Utility\ObjectAccess;
use Wazisera\Utility\JsonViewConfiguration\Annotations\Descend;
use Wazisera\Utility\JsonViewConfiguration\Annotations\Exclude;
use Wazisera\Utility\JsonViewConfiguration\Annotations\ExposeObjectIdentifier;

class JsonViewConfigurationService {

    /**
     * @var ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * @param object|array $value
     * @param array $propertyConfiguration
     * @return array
     */
    public function buildConfiguration($value, $propertyConfiguration = array()) {
        $configuration = array();

        if((is_array($value) || $value instanceof \ArrayAccess)) {
            if($this->isArraySequential($value) == true) {
                $numberOfItems = count($value);
                if($numberOfItems > 0 && is_object($value[0])) {
                    $subConfiguration = array_merge($propertyConfiguration, $this->buildConfiguration($value[0]));

                    for ($i = 0; $i < $numberOfItems; $i++) {
                        $configuration[$i] = $subConfiguration;
                    }
                }
            } else {
                foreach($value as $elementKey => $elementValue) {
                    if(is_string($elementKey) ) {
                        $configuration[$elementKey] = $this->buildConfiguration($elementValue);
                    }
                }
            }
        } else if(is_object($value)) {
            $configuration = $this->buildConfigurationForObject($value);
        }

        return $configuration;
    }

    /**
     * @param object $object
     * @return array
     */
    protected function buildConfigurationForObject($object) {
        $configuration = array();
        $className = get_class($object);

        if($this->reflectionService->isClassAnnotatedWith($className, ExposeObjectIdentifier::class)) {
            $configuration['_exposeObjectIdentifier'] = true;
        }

        /** @var Exclude $excludeAnnotation */
        $excludeAnnotation = $this->reflectionService->getClassAnnotation($className, Exclude::class);
        if($excludeAnnotation !== null) {
            $configuration['_exclude'] = $excludeAnnotation->properties;
        }

        $propertyNames = ObjectAccess::getGettablePropertyNames($object);
        foreach($propertyNames as $propertyName) {
            $buildSubConfiguration = false;

            if($this->reflectionService->isPropertyAnnotatedWith($className, $propertyName, Descend::class)) {
                $configuration['_descend'][$propertyName] = array();
                $buildSubConfiguration = true;
            }

            if($buildSubConfiguration === true) {
                $propertyValue = ObjectAccess::getProperty($object, $propertyName);

                $propertyConfiguration['_exclude'] = $this->getExcludeProperties($className, $propertyName);

                if (is_object($propertyValue) && $propertyValue !== $object && $propertyValue instanceof \DateTimeInterface == false) {
                    $subConfiguration = $this->buildConfiguration($propertyValue, $propertyConfiguration);

                    if ($subConfiguration !== array()) {
                        if (isset($configuration['_descend'][$propertyName])) {
                            $configuration['_descend'][$propertyName] = $subConfiguration;
                        } else {
                            $configuration[$propertyName] = $subConfiguration;
                        }
                    }
                }
            }
        }
        return $configuration;
    }

    /**
     * @param string $className
     * @param string $propertyName
     * @return array
     */
    protected function getExcludeProperties($className, $propertyName) {
        /** @var Exclude $excludeAnnotation */
        $excludeAnnotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, Exclude::class);
        if($excludeAnnotation != null && is_array($excludeAnnotation->properties)) {
            return $excludeAnnotation->properties;
        }
        return array();
    }

    /**
     * @param array|\ArrayAccess $array
     * @return bool
     */
    protected function isArraySequential(&$array) {
        if($array instanceof Collection) {
            return true;
        }
        if($array instanceof \ArrayAccess) {
            $array = (array)$array;
        }
        if(is_array($array)) {
            return count(array_filter(array_keys($array), 'is_string')) === 0;
        }
    }

}
