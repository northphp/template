<!doctype html>
<html>
  <head>
    <title><?php echo $this->escape($title) ?></title>
  </head>
  <body>
    <?php $this->block('content') ?>
        <p>Hello parent block</p>
    <?php $this->endblock() ?>

    <footer class="footer"><?php $this->yield('footer') ?></footer>
  </body>
</html>
