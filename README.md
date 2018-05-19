# Configuration annotations for the JsonView of Neos Flow

This Flow package allows you to create a JsonView configuration with annotations.

## Example

```php
use Wazisera\Utility\JsonViewConfiguration\Annotations as JsonViewConfig;

/**
 * @JsonViewConfig\ExposeObjectIdentifier("customObjectId")
 */
class Example {
    
    /**
     * @var string
     */
    protected $name = 'John';
    
    /**
     * @var Address
     * @JsonViewConfig\Descend(onlyForVariant="withAddress")
     */
    protected $address;
    
}
```

In the controller or an extended JsonView you can use it like:
```php
$config = $this->jsonViewConfiguration->buildConfiguration($value);
$this->view->setConfiguration($config);
```
```json
{ "name": "John" }
```

or with variant:

```php
$config = $this->jsonViewConfiguration->buildConfiguration($value, 'withAddress');
$this->view->setConfiguration($config);
```
```json
{ "name": "John", "adress": { "street": "...", "city": "..." } }
```


## Available Annotations

**Descend**
 
| Name | Type | |
|------|------|---|
| ignoreForVariant | string/array | will be ignored for specified variant |
| onlyForVariant | string/array | will only be used for specified variant |

**Exclude**
 
| Name | Type | |
|------|------|---|
| properties | string/array | excludes the given properties |
| ignoreForVariant | string/array |  |
| onlyForVariant | string/array |  |

**Only**
 
| Name | Type | |
|------|------|---|
| properties | string/array |  uses only the given properties  |
| ignoreForVariant | string/array |  |
| onlyForVariant | string/array |  |

**ExposeClassName**
 
| Name | Type | |
|------|------|---|
| qualifiedName | boolean |  should expose the full namespace  |
| ignoreForVariant | string/array |  |
| onlyForVariant | string/array |  |

**ExposeObjectIdentifier**
 
| Name | Type | |
|------|------|---|
| identifierKey | string |  the key name for the object ifentifier  |
| ignoreForVariant | string/array |  |
| onlyForVariant | string/array |  |


## Licence

This package is licensed under the MIT licence
