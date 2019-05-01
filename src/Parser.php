<?php

namespace North\Template;

class Parser
{
    /**
     * Control structures with alternative syntax.
     *
     * @see https://php.net/manual/en/control-structures.alternative-syntax.php
     *
     * @var array
     */
    protected $controls = [
        'if',
        'end if',
        'endif',
        'for',
        'foreach',
        'switch',
        'where'
    ];

    /**
     * Parse input text and replace template tags with php tags.
     *
     * @param  string $text
     *
     * @return string
     */
    public function parse($text)
    {

        // Add colon at the end of control structures.
        $text = preg_replace('/((' . implode('|', $this->controls) . ').*\)).*(\%)/', '${1}: %', $text);

        // Add colon at the end of case.
        $text = preg_replace('/((case|default.*)(?:\%))/', '${2}: %', $text);

        // Remove tabs and spaces before the first case in switch statement to prevent syntax error.
        // See https://php.net/manual/en/control-structures.alternative-syntax.php
        $text = preg_replace_callback('/(\{\s*\%\s+switch.*\%\s*\})\n(\s+)(?!:\{\s*\%)/', function ($matches) {
            return $matches[1] . "\n";
        }, $text);

        // Automatic add break to case or default.
        $text = preg_replace_callback('/((case|default).*)\n.*\{\%\s+(\w+)/', function ($matches) {
            // Allow custom break statement.
            if (trim($matches[3]) === 'break') {
                return $matches[0];
            }

            // Allow custom fallthroguh statement.
            if (trim($matches[3]) === 'fallthrough') {
                return $matches[0];
            }

            return $matches[1] . "\n{% break %}\n{% " . $matches[3];
        }, $text);

        // Remove fallthrough statement.
        $text = preg_replace('/\{\s*\%\s*fallthrough\s*\%\s*\}/', '', $text);

        // Speedup scanning.
        $encoding = null;
        if (function_exists('mb_internal_encoding') && ini_get('mbstring.func_overload') & 2) {
            $encoding = mb_internal_encoding();
            mb_internal_encoding('ASCII');
        }

        $len = strlen($text);
        $state = '';
        $ccount = 0;
        $skip = 0;
        $after = '';
        $before = '';

        for ($i = 0; $i < $len; $i++) {
            $before .= $text[$i];

            if ($skip > 0) {
                $skip--;
                continue;
            }

            if ($skip < 0) {
                $skip = 0;
            }

            switch ($text[$i]) {
                case '{':
                    # Starting var output.
                    if ($ccount === 1 && $text[$i-1] === '{') {
                        $after .= $this->start();
                        $after .= $this->method('escape');

                        if ($text[$i +1] === '{') {
                            $skip++;
                        }
                    } elseif ($ccount > 1) {
                        # All after start of var output.
                        $after .= $text[$i];
                    }

                    $ccount++;

                    break;
                case '}':
                    $ccount -= 1;

                    if ($ccount <= 0 && $text[$i-1] === '}') {
                        # Ending var output.
                        $after .= ')';
                        $after .= $this->end();
                    } elseif ($ccount > 1) {
                        # All before end of var output.
                        $after .= $text[$i];
                    }

                    break;
                case '!':
                    # Start non escaping var output.
                    if ($text[$i-1] === '{') {
                        $after .= $this->start();
                    } elseif ($text[$i] === '!') {
                        $n = $i;

                        while ($text[$n + 1] === '!') {
                            $n++;
                        }

                        $skip += $n - $i;

                        if ($text[$n + 1] === '}') {
                            $after .= trim($this->end());
                        }
                    } else {
                        # All other text than exclamation.
                        $after .= $text[$i];
                    }

                    break;
                case '%':
                    # End code output %}
                    if ($text[$i+1] === '}') {
                        $after .= trim($this->end());
                    } else {
                        # Start code output {%
                        $after .= trim($this->start(false));
                    }
                    break;
                case ' ':
                    $after .= $text[$i];
                    break;
                default:
                    $after .= $text[$i];
                    break;
            }
        }

        if ($encoding) {
            mb_internal_encoding($encoding);
        }

        $text = str_replace($before, $after, $text);

        // Remove empty blank lines.
        $text = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);

        return $text;
    }

    /**
     * Start php tag with or without echo.
     *
     * @param  bool $echo
     *
     * @return string
     */
    protected function start($echo = true)
    {
        if ($echo) {
            return '<?php echo ';
        }

        return '<?php ';
    }

    /**
     * Stop php tag.
     *
     * @return string
     */
    protected function end()
    {
        return ' ?>';
    }

    /**
     * Start method.
     *
     * @param  string $name
     *
     * @return string
     */
    protected function method($name)
    {
        return sprintf('$this->%s(', $name);
    }
}
