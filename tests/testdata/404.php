<?php $this->extend('layouts/app', ['title' => '404 - Not found']); ?>
<?php $this->block('content'); ?>
    <?php $this->include('partials/content', ['title' => '404 - Not found']); ?>
<?php $this->endblock();
