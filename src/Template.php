<?php

namespace North\Template;

use Exception;

class Template
{
    /**
     * Defeault section.
     *
     * @var array
     */
    protected $defaultSection = [
        'parent' => '',
        'text'   => '',
    ];

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
     * Template parser.
     */
    protected $parser;

    /**
     * Template constructor.
     *
     * @param array $paths
     * @param bool  $parser
     */
    public function __construct($paths, $parser = true)
    {
        $this->paths = is_array($paths) ? $paths : [$paths];

        if ($parser) {
            $this->parser = new Parser;
        }
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
     * Call callable code in arrays.
     *
     * @param  array $args
     *
     * @return array
     */
    protected function callData(array $args = [])
    {
        foreach ($args as $i => $arg) {
            if (is_callable($arg)) {
                $args[$i] = call_user_func($arg);
                continue;
            }

            if (is_array($arg)) {
                $args[$i] = $this->callData($arg);
                continue;
            }
        }

        return $args;
    }

    /**
     * Find template file to include.
     *
     * @param  string $file
     *
     * @return string|null
     */
    protected function file($file)
    {
        $name = basename($file, $this->extension);
        $file = str_replace($name, str_replace('.', '/', $name), $file);
        $file = str_replace($this->extension, '', $file);

        if (file_exists($file . $this->extension)) {
            return $file . $this->extension;
        }

        foreach ($this->paths as $path) {
            $path = $path . '/' . $file . $this->extension;

            if (file_exists($path)) {
                return $path;
            }
        }

        $message = "Search paths: \n- " . implode("\n- ", $this->paths);
        $file = $file . $this->extension;
        (new TemplateError($message, $file))->render();
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
            extract($this->callData($data));
        }

        $text = file_get_contents($file);

        if (!is_null($this->parser)) {
            $text = $this->parser->parse($text);
        }

        $tmp  = tmpfile();

        fwrite($tmp, $text);
        $output = include stream_get_meta_data($tmp)['uri'];
        fclose($tmp);

        $content = $this->layout;

        foreach ($this->sections as $key => $row) {
            $content = str_replace($this->key($key), $row['text'], $content);
        }

        echo $content;
    }

    /**
     * Render view template.
     *
     * @param  string $file
     * @param  array  $data
     *
     * @return string
     */
    protected function view($file, array $data = [])
    {
        ob_start();
        $this->render($file, $data);
        return ob_get_clean();
    }

    /**
     * Start block.
     *
     * @param  string $name
     */
    public function block($name)
    {
        $args = array_slice(func_get_args(), 1);

        $this->parent = false;
        if (!isset($this->sections[$name])) {
            $this->yield($name);
            $this->parent = true;
        }

        $this->block = $name;
        $this->sections[$name]['text'] .= implode('', $this->callData($args));

        if (empty($args)) {
            ob_start();
        }
    }

    /**
     * End block.
     */
    public function endblock()
    {
        if ($this->parent) {
            if (!isset($this->sections[$this->block])) {
                $this->sections[$this->block] = $this->defaultSection;
            }

            $this->sections[$this->block]['parent'] .= ob_get_clean();
            return;
        }

        $this->sections[$this->block]['text'] .= ob_get_clean();
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
     * @param  string $file
     * @param  array  $data
     *
     * @return string
     */
    public function fetch($file, array $data = [])
    {
        $template = new static($this->paths, !is_null($this->parser));
        return $template->view($file, $data);
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
     * @param  string $file
     * @param  array  $data
     */
    public function include($file, array $data = [])
    {
        echo $this->fetch($file, $data);
    }

    /**
     * Render parent block.
     */
    public function parent()
    {
        if (!isset($this->sections[$this->block])) {
            return;
        }

        if (!is_string($this->sections[$this->block]['parent'])) {
            return;
        }

        echo $this->sections[$this->block]['parent'];
    }

    /**
     * Render the contents of a given section.
     *
     * @param  string $name
     */
    public function yield($name)
    {
        if (!isset($this->sections[$name])) {
            $this->sections[$name] = $this->defaultSection;
        }

        echo $this->key($name);
    }
}
