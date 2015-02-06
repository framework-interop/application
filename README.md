Kernel application for framework-interop
========================================

This package contains a sample application kernel that knows how to register framework-agnostic **modules**
compatible with [framework-interop's `ModuleInterface`](https://github.com/framework-interop/module-interface).

##Framework-agnostic modules?

Have a look at [the demo](http://github.com/framework-interop/framework-interop-demo).
It shows 3 modules, one using Symfony 2, one using Silex and one using Zend Framework 1.

## What is this kernel about?

A module can provide a DI container and some instructions to be executed on init.

Modules are registered by an `Application` class that acts as a kernel.
The `Application` class will register all kernels and will take care of building the
root container and calling all `init` methods of all modules.

Also, the `Application` class will be in charge of dispatching the HTTP requests.

**Important**: using the `Application` class is entirely **optional**. You could very
well write your own `Application` class instead of using this one. The only important part
in "framework-interop" is the `ModuleInterface` interface that must be respected by
every module. How these modules are consumed is not a concern. This `Application`
class is only provided because it can be useful.

## Using the Application class
### Getting an instance of an application

Simply write an **app.php** file with this code:

**app.php**
```php
<?php
use Interop\Framework\Application;
use Acme\CoreModule\CoreModule;
use Acme\BlogModule\BlogModule;
use Acme\BackOfficeModule\BackOfficeModule;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application(
    [
        CoreModule::class,
        BlogModule::class,
        BackOfficeModule::class,
    ]
);
```

The list of registered modules is passed as a parameter to `Application`.

### Running the application

In order to run your application, you just need an `index.php` file:

**index.php**
```php
require 'app.php';

$app->runHttp();
```

### Using modules instances

You can pass instances of modules to the application instead of fully qualified
class names.

**app.php**
```php
<?php
use Interop\Framework\Application;
use Acme\CoreModule\CoreModule;
use Acme\BlogModule\BlogModule;
use Acme\BackOfficeModule\BackOfficeModule;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application(
    [
        new CoreModule(),
        new BlogModule(),
        new BackOfficeModule('param'),
    ]
);
```
