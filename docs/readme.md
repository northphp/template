## Introduction

Template is a simple native PHP template engine. Since it's a native PHP template engine you are allowed to use PHP code inside your views.

## Defining A Layout

```php
<!doctype html>
<html>
  <head>
    <title><?php echo $this->escape($title) ?></title>
  </head>
  <body>
    <?php $this->block('content') ?>
        <p>This is the parent content</p>
    <?php $this->endblock() ?>

    <footer class="footer"><?php $this->yield('footer') ?></footer>
  </body>
</html>
```

You chould also use `$this->yield` for rendering the title from the child.

```php
<title><?php $this->yield('title') ?></title>
```

```php
<?php $this->block('title', 'Page title') ?>
```

The main different between `yield` and `block` is that it can't have parent content.

## Extending A Layout

```php
<?php $this->extend('layouts/app', ['title' => 'Page title']) ?>
<?php $this->block('content') ?>
    <?php $this->parent() ?>
    This is the child content
<?php $this->endblock();
```

## Components and Slots

Components and slots provide similar benefits to blocks and layouts. It may be easier to understand than blocks and layouts.

The default components directory in paths are `components`

```php
<div class="alert alert-danger">
    <?php echo $slot; ?>
</div>
```

```php
<?php $this->component('alert') ?>
    <strong>Whoops!</strong> Something went wrong!
<?php $this->endcomponent() ?>
```

The `$slot` variable will contain the content we wish to inject into the component. The component can take in more data by sending in a array of data to `$this->component` as a second argument.

```php
<?php $this->component('alert', ['title' => 'Test']) ?>
```

```php
<div class="alert alert-danger">
    <h2><?php echo $title; ?></h2>
    <?php echo $slot; ?>
</div>
```

## Rendering template

```php
use North\Template\Template;

$template = new Template([
  'paths' => 'path/to/templates',
]);

$template->render('index');
```

## Add custom functions

You can add custom functions to the template engine and use it in your templates.

```php
use North\Template\Template;

$template = new Template([
  'paths' => 'path/to/templates',
]);

$template->addFunction('up', function ($t) {
    return strtoupper($t);
});
```

```php
echo $this->up('Hello');
```

## Filters

The template engine supports filters that you can use to transform your data before rendering it. Filters are existing functions in php like `strtoupper` or `strip_tags`. Use '|' between filters to apply multiple filters.

```php
echo $this->filter('<p>Hello</p>', 'strip_tags|escape');
```

`escape` filter is our custom escape method. To add support for custom functions you have to update the `filters` option:

```php
use North\Template\Template;

$template = new Template([
  'filters' => ['escape', 'customFunction'],
  'paths'   => 'path/to/templates',
]);
```

## Escape

The escape method escape the string before output.

```php
echo $this->escape('<p>Hello</p>');
```