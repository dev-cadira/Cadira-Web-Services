
Cadira REST API client for PHP

```php
require 'vendor/autoload.php';
require_once 'WSCadira.php';

$cadira = new Cadira('VTIGER_URL', 'VTIGER_USER', 'VTIGER_ACCESSKEY');
```

##### Create

```php
$params      = [
    'assigned_user_id'         => '1',
    'subject'                  => 'Test',
    'quotestage'               => 'Created',
    'productid'                => '14x3',
    'description'              => 'Test Description',
    'hdnTaxType'               => 'group', // group or individual taxes are obtained from the application
    'LineItems'                => [
        '0' => [
            'productid'        => '14x3',
            'sequence_no'      => '1',
            'quantity'         => '1.000',
            'listprice'        => '500.00',
            'comment'          => 'sample comment product',
        ],

    ],
];

$result = $cadira->create($params, 'Quotes');
```

#### Update
```php
$params = ['id' => '12x3654', 'lastname' => 'Test Lead', 'email' => 'test@test.com', 'assigned_user_id' => '19x1'];
$result = $cadira->update($params);
```

#### Retrieve
```php
$result = $cadira->retrieve('5x3679');
```

#### Revise
```php
$params = ['id' => '12x3653', 'email' => 'test2@test.com', 'assigned_user_id' => '19x1'];
$result = $cadira->revise($params);
```

#### Describe
```php
$result = $cadira->describe('Contacts');
```
#### Query
```php
$params = ['email' => 'test2@test.com'];
$select = ['mobile'];

$result = $cadira->query('Contacts', $params, $select);
```

#### ListTypes
```php
$result = $cadira->listTypes();
```

#### RetrieveRelated
```php
$result = $cadira->retrieveRelated('12x3653', 'Activities', 'Calendar');
```