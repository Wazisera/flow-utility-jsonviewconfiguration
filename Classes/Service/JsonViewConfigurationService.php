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
use Wazisera\Utility\JsonViewConfiguration\Annotations\AbstractAnnotation;
use Wazisera\Utility\JsonViewConfiguration\Annotations\Descend;
use Wazisera\Utility\JsonViewConfiguration\Annotations\Exclude;
use Wazisera\Utility\JsonViewConfiguration\Annotations\ExposeClassName;
use Wazisera\Utility\JsonViewConfiguration\Annotations\ExposeObjectIdentifier;
use Wazisera\Utility\JsonViewConfiguration\Annotations\Only;

class JsonViewConfigurationService {

    /**
     * @var ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * @param object|array $value
     * @param string $variant
     * @return array
     */
    public function buildConfiguration($value, $variant = '') {
        return $this->buildConfigurationValue($value, array(), $variant);
    }

    /**
     * @param object|array $value
     * @param array $propertyConfiguration
     * @param string $variant
     * @return array
     */
    protected function buildConfigurationValue($value, $propertyConfiguration = array(), $variant) {
        $configuration = array();

        if((is_array($value) || $value instanceof \ArrayAccess)) {
            if($this->isArraySequential($value) == true) {
                $numberOfItems = count($value);
                if($numberOfItems > 0 && is_object($value[0])) {
                    $subConfiguration = array_merge($this->buildConfigurationValue($value[0], $propertyConfiguration, $variant));

                    for ($i = 0; $i < $numberOfItems; $i++) {
                        $configuration[$i] = $subConfiguration;
                    }
                }
            } else {
                foreach($value as $elementKey => $elementValue) {
                    if(is_string($elementKey) ) {
                        $configuration[$elementKey] = $this->buildConfigurationValue($elementValue, array(), $variant);
                    }
                }
            }
        } else if(is_object($value)) {
            $configuration = $this->buildConfigurationForObject($value, $variant);
        }

        return $configuration;
    }

    /**
     * @param object $object
     * @param string $variant
     * @return array
     */
    protected function buildConfigurationForObject($object, $variant) {
        $configuration = array();
        $className = get_class($object);

        $this->configureExposeObjectIdentifier($configuration, $className, null, $variant);
        $this->configureExposeClassName($configuration, $className, null, $variant);
        $this->configureOnlyProperties($configuration, $className, null, $variant);
        $this->configureExcludeProperties($configuration, $className, null, $variant);

        $propertyNames = ObjectAccess::getGettablePropertyNames($object);
        foreach($propertyNames as $propertyName) {
            $buildSubConfiguration = false;

            /** @var Descend $descendAnnotation */
            $descendAnnotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, Descend::class);
            if($descendAnnotation != null && $this->shouldConfigureAnnotationForVariant($descendAnnotation, $variant)) {
                $configuration['_descend'][$propertyName] = array();
                $buildSubConfiguration = true;
            }

            if($buildSubConfiguration === true) {
                $propertyValue = ObjectAccess::getProperty($object, $propertyName);

                $propertyConfiguration = array();

                $this->configureExposeObjectIdentifier($propertyConfiguration, $className, $propertyName);
                $this->configureExposeClassName($propertyConfiguration, $className, $propertyName);
                $this->configureOnlyProperties($propertyConfiguration, $className, $propertyName);
                $this->configureExcludeProperties($propertyConfiguration, $className, $propertyName);

                if (is_object($propertyValue) && $propertyValue !== $object && $propertyValue instanceof \DateTimeInterface == false) {
                    $subConfiguration = $this->buildConfigurationValue($propertyValue, $propertyConfiguration, $variant);
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
     * @param array $configuration
     * @param string $className
     * @param string $propertyName
     * @param string $variant
     */
    protected function configureExcludeProperties(array &$configuration, $className, $propertyName = null, $variant = '') {
        if($propertyName === null) {
            $excludeAnnotation = $this->reflectionService->getClassAnnotation($className, Exclude::class);
        } else {
            $excludeAnnotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, Exclude::class);
        }
        if($excludeAnnotation != null && $this->shouldConfigureAnnotationForVariant($excludeAnnotation, $variant) && is_array($excludeAnnotation->properties)) {
            $configuration['_exclude'] = $excludeAnnotation->properties;
        }
    }

    /**
     * @param array $configuration
     * @param string $className
     * @param string $propertyName
     * @param string $variant
     */
    protected function configureOnlyProperties(array &$configuration, $className, $propertyName = null, $variant = '') {
        if($propertyName === null) {
            $onlyAnnotation = $this->reflectionService->getClassAnnotation($className, Only::class);
        } else {
            $onlyAnnotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, Only::class);
        }
        if($onlyAnnotation != null && $this->shouldConfigureAnnotationForVariant($onlyAnnotation, $variant) && is_array($onlyAnnotation->properties)) {
            $configuration['_only'] = $onlyAnnotation->properties;
        }
    }

    /**
     * @param array $configuration
     * @param string $className
     * @param string $propertyName
     * @param string $variant
     */
    protected function configureExposeObjectIdentifier(array &$configuration, $className, $propertyName = null, $variant = '') {
        if($propertyName === null) {
            $exposeIdentifierAnnotation = $this->reflectionService->getClassAnnotation($className, ExposeObjectIdentifier::class);
        } else {
            $exposeIdentifierAnnotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, ExposeObjectIdentifier::class);
        }
        if($exposeIdentifierAnnotation != null && $this->shouldConfigureAnnotationForVariant($exposeIdentifierAnnotation, $variant)) {
            $configuration['_exposeObjectIdentifier'] = true;
            if(strlen($exposeIdentifierAnnotation->identifierKey) > 0) {
                $configuration['_exposedObjectIdentifierKey'] = $exposeIdentifierAnnotation->identifierKey;
            }
        }
    }

    /**
     * @param array $configuration
     * @param string $className
     * @param string $propertyName
     * @param string $variant
     */
    protected function configureExposeClassName(array &$configuration, $className, $propertyName = null, $variant = '') {
        if($propertyName === null) {
            $exposeClassNameAnnotation = $this->reflectionService->getClassAnnotation($className, ExposeClassName::class);
        } else {
            $exposeClassNameAnnotation = $this->reflectionService->getPropertyAnnotation($className, $propertyName, ExposeClassName::class);
        }
        if($exposeClassNameAnnotation != null && $this->shouldConfigureAnnotationForVariant($exposeClassNameAnnotation, $variant)) {
            $configuration['_exposeClassName'] = ($exposeClassNameAnnotation->qualifiedName === true) ? 1 : 2;
        }
    }

    /**
     * @param object $annotation
     * @param string $variant
     * @return bool
     */
    protected function shouldConfigureAnnotationForVariant($annotation, $variant = '') {
        if($annotation != null && $annotation instanceof AbstractAnnotation) {
            if(in_array($variant, $annotation->ignoreForVariant)) {
                return false;
            }
            if(count($annotation->onlyForVariant) > 0 && in_array($variant, $annotation->onlyForVariant) === false) {
                return false;
            }
            return true;
        }
        return false;
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
