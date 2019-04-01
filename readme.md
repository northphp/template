# Template

> Work in progress

[![Build Status](https://travis-ci.org/northphp/template.svg?branch=master)](https://travis-ci.org/northphp/template)

A simple template system.

## Installation

```
composer require north/template
```

## Examples

```php
use North\Template\Template;

$template = new Template('path/to/templates');

$template->render('index');
```

`index.php`
```php
<?php $this->extend('layouts/app', ['title' => 'Startpage']) ?>
<?php $this->block('content') ?>
    <?php $this->include('partials/content', ['title' => 'Hello, world']) ?>
<?php $this->endblock() ?>
```

`layouts/app.php`
```php
<!doctype html>
<html>
  <head>
    <title><?php echo $this->escape($title) ?></title>
  </head>
  <body>
    <?php $this->section('content') ?>
  </body>
</html>
```

`partials/content.php`
```php
<div class="content">
    <h1><?php echo $this->escape($title) ?></h1>
</div>
```

More examples in the [`tests/TemplateTest.php`](tests/TemplateTest.php)

## License

MIT © [Fredrik Forsmo](https://github.com/frozzare)