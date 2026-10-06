## Subclassing

To use a subclass of [`Document`](Classes/Domain/Model/Document.php) a database mapping has to be defined.

1. Let your class extend `\Cundd\DocumentStorage\Domain\Model\Document`:

```php
<?php

declare(strict_types=1);

namespace Vendor\Extension\Domain\Model;

class MyClass extends \Cundd\DocumentStorage\Domain\Model\Document {}
```

2. Define the database mapping in `Configuration/Extbase/Persistence/Classes.php`:

> See [TYPO3 Explained: Table and field mapping](https://docs.typo3.org/permalink/t3coreapi:extbase-manual-mapping)
> for more information

```php
<?php

declare(strict_types=1);

return [
    \Vendor\Extension\Domain\Model\MyClass::class =>  [
        'tableName'  => \Cundd\DocumentStorage\Constants::DOCUMENTS_TABLE_NAME,
        'properties' => \Cundd\DocumentStorage\Constants::DOCUMENTS_FIELD_MAPPING,
    ],
];
```
