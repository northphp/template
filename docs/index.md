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

## Rendering template

```php
use North\Template\Template;

$template = new Template('path/to/templates');

$template->render('index');
```