## Purpose

* Run Symfony apps under [React-PHP](https://github.com/react-php).

Note: This bundle hasn't got cookie support yet, if you need session handling, use Symfony HTTP Basic Auth header when handling
user accounting in Symfony.

## Installation

### Composer

To install the bundle through Composer, run the following command in console at your project base path:

```
php composer.phar require jogaram/symfony-react-bundle
```

Then register the new bundle in your AppKernel.

```php
<?php
    
    // #app/AppKernel.php
    $bundles = array(
        ...
        new Jogaram\ReactPHPBundle\JogaramReactPHPBundle(),
        ...
    );
    
```
