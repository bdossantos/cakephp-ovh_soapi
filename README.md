# Cakephp OVH Soapi Model

Inspired of CakePHP SOAP datasource : https://github.com/cakephp/datasources/blob/master/models/datasources/soap_source.php

## Usage :

```php
App::uses('OvhSoapi', 'OvhSoapi.Model');
$this->Soapi = new OvhSoapi(
    'login' => 'xxxxxx-ovh',
    'password' => '******'
    'location' => 'fr'
);

$servers = $this->Soapi->query('dedicatedList');
```

see full doc here :

http://www.ovh.com/soapi/fr/

## Todo :

* Refactor as datasource ?
