{% $this->extend('layouts/app', ['title' => '404 - Not found']) %}
{% $this->block('content') %}
    {% $this->parent() %}
    {% $this->include('partials/content', ['title' => '404 - Not found']) %}
    {!! $this->fetch('partials/content', ['title' => 'Fetch - Not found']) !!}
    {{ '<a href="#">Click</a>' }}
{% $this->endblock(); %}