Address Parser
==============

**Address Parser** is a library that can parse most dutch addresses.

[![Build Status](https://travis-ci.org/treehouselabs/address-parser.svg?branch=master)](https://travis-ci.org/treehouselabs/address-parser)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/treehouselabs/address-parser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/treehouselabs/address-parser/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/treehouselabs/address-parser/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/treehouselabs/address-parser/?branch=master)

Usage
=====

```php
$parser = new \TreeHouse\AddressParser\AddressParser();

$result = $parser->parse('Willembuytechweg 45');

var_dump($result);
```

The above example will output:

```php
array(3) {
  'street' =>
  string(16) "Willembuytechweg"
  'number' =>
  string(2) "45"
  'address' =>
  string(19) "Willembuytechweg 45"
}
```
