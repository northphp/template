<?php

namespace North\Template;

use Exception;

class TemplateError extends Exception
{
    /**
     * Error arguments.
     *
     * @var array
     */
    protected $args = [
        'title'   => 'Error',
        'message' => 'An error occurred while rendering the template for this page.',
        'file'    => '',
        'code'    => '',
    ];

    /**
     * Error construct.
     *
     * @param array $args
     */
    public function __construct($message = null, $file = '')
    {
        parent::__construct($this->args['message']);
        $this->args['code'] = $message;
        $this->args['file'] = $file;
    }

    /**
     * Render error as html.
     */
    public function render()
    {
        if (defined('STDIN')) {
            throw $this;
        } else {
            extract($this->args);
            require_once __DIR__ . '/views/error.php';
        }
    }
}
