<?php

namespace North\Template;

use Exception;

class Template
{
    /**
     * Template extension.
     */
    protected $extension = '.php';

    /**
     * Custom functions.
     *
     * @var array
     */
    protected $functions = [];

    /**
     * Current layout.
     *
     * @var string
     */
    protected $layout = '';

    /**
     * Template paths.
     *
     * @var string
     */
    protected $paths = [];

    /**
     * Template sections.
     *
     * @var array
     */
    protected $sections = [];

    /**
     * Parent template sections.
     *
     * @var array
     */
    protected $parentSections = [];

    /**
     * Template constructor.
     *
     * @param array $paths
     */
    public function __construct($paths)
    {
        $this->paths = is_array($paths) ? $paths : [$paths];
    }

    /**
     * Dynamic call custom functions.
     *
     * @param  string $name
     * @param  array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (isset($this->functions[$name])) {
            return call_user_func_array($this->functions[$name], $arguments);
        }
    }

    /**
     * Add custom function.
     *
     * @param  string   $name
     * @param  callable $callback
     */
    public function addFunction($name, $callback)
    {
        $this->functions[$name] = $callback;
    }

    /**
     * Find template file to include.
     *
     * @param  string $template
     *
     * @return string|null
     */
    protected function file($template)
    {
        $name = basename($template, $this->extension);
        $template = str_replace($name, str_replace('.', '/', $name), $template);
        $template = str_replace($this->extension, '', $template);

        if (file_exists($template . $this->extension)) {
            return $template . $this->extension;
        }

        foreach ($this->paths as $path) {
            $path = $path . '/' . $template . $this->extension;

            if (file_exists($path)) {
                return $path;
            }
        }

        throw new Exception(sprintf('Template file could not be found: %s', $template));
    }

    /**
     * Get section key.
     *
     * @param  string $key
     *
     * @return string
     */
    protected function key($key)
    {
        return '<?=' . $key . '?>';
    }

    /**
     * Render template.
     *
     * @param  string $file
     * @param  array  $data
     */
    public function render($file, array $data = [])
    {
        $file = $this->file($file);

        if (! file_exists($file)) {
            return;
        }

        if (! empty($data) && is_array($data)) {
            extract($data);
        }

        include $file;

        $content = $this->layout;

        foreach ($this->sections as $key => $value) {
            $content = str_replace($this->key($key), $value, $content);
        }

        echo $content;
    }

    /**
     * Render view template.
     *
     * @param  string $template
     * @param  array $data
     *
     * @return string
     */
    protected function view($template, array $data = [])
    {
        $template = $this->file($template);

        if (! file_exists($template)) {
            return;
        }

        if (! empty($data) && is_array($data)) {
            extract($data);
        }

        ob_start();

        include $template;

        return ob_get_clean();
    }

    /**
     * Start block.
     *
     * @param  string $name
     */
    public function block($name)
    {
        $this->parent = false;

        if (!isset($this->sections[$name])) {
            $this->section($name);
            $this->parent = true;
        }

        $this->block = $name;
        ob_start();
    }

    /**
     * End block.
     */
    public function endblock()
    {
        if ($this->parent) {
            if (!isset($this->parentSections[$this->block])) {
                $this->parentSections[$this->block] = '';
            }

            $this->parentSections[$this->block] .= ob_get_clean();
            return;
        }

        $this->sections[$this->block] .= ob_get_clean();
    }

    /**
     * Esacpe text.
     *
     * @see https://www.php.net/htmlspecialchars
     *
     * @param  string $value
     * @param  int    $flags
     * @param  string $encoding
     *
     * @return string
     */
    public function escape($value, $flags = ENT_COMPAT | ENT_HTML401, $encoding = 'UTF-8')
    {
        return htmlspecialchars($value, $flags, $encoding);
    }

    /**
     * Extend layout.
     *
     * @param  string $template
     * @param  array $data
     */
    public function extend($template, array $data = [])
    {
        $this->layout = $this->view($template, $data);
    }

    /**
     * Fetch template view to string.
     *
     * @param  string $template
     * @param  array $data
     *
     * @return string
     */
    public function fetch($template, array $data = [])
    {
        return $this->view($template, $data);
    }

    /**
     * Apply filter functions to variable.
     *
     * @param  string $value
     * @param  string $functions
     *
     * @return mixed
     */
    public function filter($value, $functions)
    {
        foreach (explode('|', $functions) as $function) {
            if (is_callable($function)) {
                $value = call_user_func($function, $value);
            } else {
                throw new Exception(sprintf('The filter function could not be found: %s', $function));
            }
        }

        return $value;
    }

    /**
     * Include template view.
     *
     * @param  string $template
     * @param  array $data
     */
    public function include($template, array $data = [])
    {
        echo $this->fetch($template, $data);
    }

    /**
     * Render parent block.
     */
    public function parent()
    {
        if (!isset($this->parentSections[$this->block])) {
            return;
        }

        if (!is_string($this->parentSections[$this->block])) {
            return;
        }

        echo $this->parentSections[$this->block];
    }

    /**
     * Start block section.
     *
     * @param  string $name
     */
    public function section($name)
    {
        if (!isset($this->sections[$name])) {
            $this->sections[$name] = '';
        }

        echo $this->key($name);
    }
}
