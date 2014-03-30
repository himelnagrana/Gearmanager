Nag Gearmanager
===================

### Version 1.0.0 [first release]

## Use

Use the file ``src/Nag/Gearmanager.php``

Extend ``Gearmanager`` class with ``config`` and ``tasks`` array.

The ``config`` will have to be as follows:

```php
$config = array (
    'host' => '127.0.0.1',
    'port' => 4730
);
```

And the ``tasks`` as:
```php
$tasks = array (
    'Task\Sendemail', 'Task\Sendsms'. ... ... ...
);
```

## Fire worker tasks

Based on the priority required for application the following functions can be used

* ``fireEvent()``
* ``fireParallel()``
* ``fireUrgent()``

#### ``fireEvent()`` and ``fireParallel()`` arguments:

* string ``$task`` [fully qualified task class name]
* array ``$payload``
* string ``$priority`` [values: normal, low, high; default value is 'normal']

#### ``fireUrgent()`` arguments:

* string ``$task`` [fully qualified task class name]
* array ``$payload``
* string ``$priority`` [values: low, high; default value is 'low']

It returns string ``$response``



## Example

```php

use Nag\Gearmanager;

$config = array (
    'host' => '127.0.0.1',
    'port' => 4730
);

$tasks = array (
    'Task\Sendemail'
);

$gearmanager = new Gearmanager($config, $tasks);

$gearmanager->fireEvent('Task\Sendemail', array('to' => 'john@doe.com', 'msg' => 'Hello!'), 'normal');

$gearmanager->fireParallel('Task\Sendemail', array('to' => 'john@doe.com', 'msg' => 'Hello!'), 'low');

$response = $gearmanager->fireUrgent('Task\Sendemail', array('to' => 'john@doe.com', 'msg' => 'Hello!'), 'high');

```