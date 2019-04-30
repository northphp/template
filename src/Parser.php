<?php

namespace North\Template;

class Parser
{
    /**
     * Parse input text and replace template tags with php tags.
     *
     * @param  string $text
     *
     * @return string
     */
    public function parse($text)
    {
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
                    } elseif ($ccount > 1 && $skip <= 0) {
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
                    } elseif ($ccount > 1 && $skip === 0) {
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
                    # Start code output {%
                    if ($text[$i+1] === '}') {
                        $after .= trim($this->end());
                    } else {
                        # End code output %}
                        $after .= trim($this->start(false));
                    }
                    break;
                default:
                    $after .= $text[$i];
                    break;
            }
        }

        if ($encoding) {
            mb_internal_encoding($encoding);
        }


        return str_replace($before, $after, $text);
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
