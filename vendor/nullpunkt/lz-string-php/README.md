lz-string-php
=============

lz-string is designed to fulfill the need of storing large amounts of data in localStorage, specifically on mobile devices. 
Due to the limitation of a maximum of 5MB in the local storage one possible solution is to compress the data right before storing 
it locally. 
In case you want to transfer your stored data from / to a php service you can use this library to (en)code the data.  

This code is originally based on the LZ-String javascript version found here: https://pieroxy.net/blog/pages/lz-string/index.html 
and is a 1:1 copy/translation into php. 

## Usage
```php
<?php
\LZCompressor\LZString::compressToBase64($rawstr);
```

## Installation

### Composer
```cmd
composer require nullpunkt/lz-string-php
```

## Changelog
### 2021-04-13
- v1.2.1 Fixed overhead that happens with PHP UTF-8 string indexing. The calculation complexity was raising exponentially 
  and became unusable when the compressed message was even less than 1MBytes. [Thanks to https://github.com/peetervois]

### 2016-03-23
- v1.2.0 Added utf16 functionality

### 2016-02-28 
- v1.1.0 Completely rewritten LZString component to match the output of js-lz-string version 1.4.4
- PHPUnit tests for continuous testing / comparision of lz-string js

### 2016-02-25 
- Added v1.0.0 to packagist/composer nullpunkt/lz-string-php

### 2016-02-04 
- Overhaul and refactor by https://github.com/Korcholis

### 2014-03-12 
- Small Bugfix added (Thanks to Filipe)

### 2014-05-09 
- Added support for special chars like é,È, ... [Thanks to https://github.com/carlholmberg]
