## Subclassing

To use a subclass of [`Document`](Classes/Domain/Model/Document.php) a database mapping has to be defined:

```php
namespace Vendor\Extension\Domain\Model;

class MyClass extends \Cundd\DocumentStorage\Domain\Model\Document {}
```

```
config.tx_extbase.persistence.classes {
    Vendor\Extension\Domain\Model\MyClass {
        mapping {
            tableName = tx_documentstorage_domain_model_document
        }
    }
}
```
