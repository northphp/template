<?php $this->extend('layouts/app', ['title' => '404 - Not found']) ?>
<?php $this->block('content') ?>
    <?php $this->include('partials/content', ['title' => '404 - Not found']) ?>
    <?php echo $this->fetch('partials/content', ['title' => 'Fetch - Not found']) ?>
    <?php echo $this->escape('<a href="#">Click</a>') ?>
<?php $this->endblock() ?>