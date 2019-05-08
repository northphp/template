<!-- comment -->
<?php $this->extend('layouts/app') ?>
<?php $this->block('content') ?>
    <?php $this->parent() ?>
    <?php $this->include('partials/content', ['title' => 'Sidebar']); ?>
<?php $this->endblock() ?>
<?php $this->block('footer', 'footer') ?>