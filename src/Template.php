<?php

namespace North\Template;

class Template
{

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
     * Include template view.
     *
     * @param  string $template
     * @param  array $data
     */
    public function include($template, array $data = [])
    {
        echo $this->view($template, $data);
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
