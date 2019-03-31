<?php

namespace North\Template;

class Template
{
    /**
     * Custom functions.
     *
     * @var array
     */
    protected $functions = [];

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
        if (file_exists($template)) {
            return $template;
        }

        foreach ($this->paths as $path) {
            $path = $path . '/' . $template . '.php';

            if (file_exists($path)) {
                return $path;
            }
        }

        return '';
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
     */
    public function render($file)
    {
        $file = $this->file($file);

        if (! file_exists($file)) {
            return;
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
        $this->block = $name;
        ob_start();
    }

    /**
     * End block.
     */
    public function endblock()
    {
        $this->sections[$this->block] = ob_get_clean();
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
     * Esacpe text.
     *
     * @see https://www.php.net/htmlspecialchars
     *
     * @param  string $text
     * @param  int    $flags
     * @param  string $encoding
     *
     * @return string
     */
    public function escape($text, $flags = ENT_COMPAT | ENT_HTML401, $encoding = 'UTF-8')
    {
        return htmlspecialchars($text, $flags, $encoding);
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
     * Start block section.
     *
     * @param  string $name
     */
    public function section($name)
    {
        $this->sections[$name] = true;
        echo $this->key($name);
    }
}
