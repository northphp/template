<!-- comment -->
<?php $this->extend('layouts/app', ['title' => '404 - Not found']) ?>
<?php $this->block('content') ?>
    <?php $this->parent() ?>
    <?php $this->include('partials/content', ['title' => '404 - Not found']); ?>
    <?php echo $this->escape('<a href="#">Click</a>') ?>
<?php $this->endblock() ?>
<?php $this->block('footer', 'footer') ?>