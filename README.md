## Introduction

*Run Symfony apps under [React-PHP](https://github.com/react-php).*

This module adds three Symfony console commands to run a server with your APP.

No configuration is needed. Follow the **Installation** instructions and read **Usage** section to know how to start using ReactPHP with your Symfony APP.

## Installation

### Composer

To install the bundle through Composer, run the following command in console at your project base path:

```
php composer.phar require jogaram/react-bundle
```

### Register bundle

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

## Usage

To start using ReactPHP with Symfony, open console, go to your project root path and execute the following command:

```
php app/console react:server:run --standalone
```

### Available options

* **--port=1337** | **-p 1337** Selects port to run server at. Defaults to 1337.
* **--standalone** If passed, React server will serve static files directly. (Use this if you don`t have Apache or Nginx running in you local machine. Static file serving is not designed for production environments)
* **--cache** If passed, class loader will be enabled.
* **--apc** If passed, APC class loader will be enabled. This option requires **--cache** option.

### Background server

This bundle comes with two more methods to run server in background. To start the server execute the following:

```
php app/console react:server:start --standalone
```
Note: If port is specified, also must be specified in stop server command.

To stop the server, run:

```
php app/console react:server:stop --standalone
```
