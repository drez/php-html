<?php

namespace App\Application\Helper\Html;

class Html
{

    /**
     * default class to use on tags
     *
     * @var array
     */
    private $classes =
    [
        'bootstrap' =>
        [
            'panel' => 'card-group',
            'panel-default' => 'card',
            'panel-heading' => 'card-header',
            'panel-body' => 'card-body',
            'container' => 'container',
            'container-fluid' => 'container-fluid',
            'jumbotron' => 'jumbotron',
            'row' => 'row',
            'col' => 'col-lg-',
            'button' => 'btn btn-outline-primary',
            'button-primary' => 'btn btn-lg btn-primary',
            'h1' => '',
            'h2' => '',
            'h3' => '',
            'ul' => '',
            'li' => '',
            'table' => 'table',

        ]
    ];
    private $id = 1;

    /**
     * html buffers
     *
     * @var string
     */
    private $head = '';
    private $html = '';
    private $buffer = '';
    private $toClose = [];

    /**
     * Markups that refers to inputs where content should be in value
     */
    private $inputs = [
        "input",
        "button",
        "checkbox",
        "textarea"
    ];

    /**
     * Produce human readable code
     *
     * @var boolean
     */
    private $humanReadable = true;

    /**
     * add incremental id to tags
     *
     * @var boolean
     */
    private $autoId = true;

    /**
     * add incremental id to tags
     *
     * @var boolean
     */
    private $autoHtml = true;

    public function __construct($framework = '', $buffer = false)
    {
        $this->isBuffered = false;
        if ($buffer) {
            $this->isBuffered = true;
        }

        $this->class = $this->classes[$framework];

        if ($this->humanReadable) {
            $this->newline = \PHP_EOL;
        } else {
            $this->newline = "";
        }
    }

    /**
     * Call markup() on undeclared functions, markups with no shorcut
     *
     * @param string $method
     * @param array $args
     *
     * @return void
     */
    public function __call($method, $args)
    {
        if (\preg_match('/^(add)(\w*)/', $method, $match)) {
            $method = strtolower($match[2]);
            $close = true;
        } else {
            $close = false;
        }

        if (\method_exists($this, $method)) {
            $this->$method($close, $args[0], $args[1]);
        } else if (\method_exists($this, 'add' . \ucfirst($method))) {
            $markup = $this->$method($close, $args[0], $args[1], $args[2]);
        } else {
            $markup = $this->markup($method, $close, $args[0], $args[1], $args[2]);
        }

        if ($args[2]) {
            return $markup;
        } else {
            return $this;
        }
    }

    /**
     * Create a HTML tag
     *
     * @param string $markup
     * @param bool $close
     * @param string $content
     * @param array $options
     * @param boolean $return
     *
     * @return void
     */
    private function markup($markup, $close, $content = '', $options = [], $return = false)
    {

        $options = $this->getOptions($markup, $options);

        if (in_array($markup, $this->inputs) && !isset($options['value'])) {
            $options['value'] = $content;
            $content = '';
        }

        if ($close) {
            $markup = $this->indent() . "<{$markup} " . $options . ">{$content}</{$markup}>{$this->newline}";
        } else {

            array_unshift($this->toClose, $markup);
            $markup = $this->indent() . "<{$markup} " . $options . ">{$this->newline}";
        }

        if ($return) {
            return $markup;
        } else {
            $this->buffer .= $markup;
        }

        return $this;
    }

    /**
     * Get the HTML tag to close the currently open tag or hierarchy
     *
     * @param boolean $all
     * @param integer $except
     *
     * @return void
     */
    public function close($all = false, $except = 0)
    {
        if ($all && count($this->toClose)) {
            $toclose = count($this->toClose) - $except;
            for ($i = 0; $i < $toclose; $i++) {
                $markup = array_pop($this->toClose);
                $this->buffer .= $this->indent() . "</{$markup}>{$this->newline}";
            }
        } else {
            $markup = array_pop($this->toClose);
            $this->buffer .= $this->indent() . "</{$markup}>{$this->newline}";
        }

        return $this;
    }

    /**
     * Set some defaults properties when necessary
     *
     * @param string $markup
     * @param array $options
     *
     * @return string
     */
    private function getOptions($markup, $options): string
    {

        if (!empty($this->class[$markup]) && !isset($options['class'])) {
            $options = array_merge($options, ['class' => $this->class[$markup]]);
        }

        if (isset($options['addclass'])) {
            $options = array_merge($options, ['class' => $options['addclass']]);
            unset($options['addclass']);
        }

        if ($this->autoId) {
            if (!isset($options['id'])) {
                $options['id'] = 'auto' . $this->getNextId();
            }
        }

        return $this->array2options($options);
    }

    /**
     * Generate the HTML properties from array
     *
     * @param array $options
     *
     * @return string
     */
    private function array2options($options)
    {
        $optionsContent = "";

        if (is_array($options)) {
            foreach ($options as $key => $data) {
                if (is_array($data)) {
                    foreach ($data as $subkey => $subdata) {
                        $optionsContent .= $key . "-" . $subkey . '="' . $subdata . '" ';
                    }
                } else {
                    $optionsContent .= $key . '="' . $data . '" ';
                }
            }

            return $optionsContent;
        } else {
            return "";
        }
    }

    /**
     * Get a number of TAB caracters in function of the open hierarchy
     *
     * @return string
     */
    private function indent(): string
    {
        if ($this->humanReadable) {
            return str_repeat("\t", count($this->toClose));
        } else {
            return "";
        }
    }

    public function getBuffer(): string
    {
        $buffer = $this->buffer;
        $this->buffer = '';
        return $buffer;
    }



    public function getNextId()
    {
        return $this->id++;
    }

    /**
     * Output
     */

    public function getPage(): string
    {
        if ($this->autoHtml) {
            $this->html .= "<!DOCTYPE html>{$this->newline}<html>{$this->newline}" . $this->buffer . "{$this->newline}</html>";
        } else {
            $this->html .= $this->buffer;
        }

        return $this->getHtml(true, true);
    }

    public function getHtml($reset = true, $head = false)
    {
        if (!empty($this->buffer)) {
            $this->html = $this->buffer;
            $this->buffer = "";
        }

        if ($this->bodyOpen) {
            $this->html .= "</body>{$this->newline}";
        }

        $html = $this->html;
        if ($reset) {
            $this->html = "";
        }

        return (($head) ? $this->head : '') . $html;
    }


    /**
     * Shortcuts
     */

    function preprint($in, $return = false)
    {
        $this->addPre(print_r($in, true));
        if ($return) {
            return $this->getHtml();
        } else {
            echo $this->getHtml();
        }
    }

    public function jumbotron($close, $content = null)
    {
        $this->markup('div', $close, $content, ['class' => $this->class['jumbotron']]);
        return $this;
    }

    public function container($close, $content = null)
    {
        $this->markup('div', $close, $content, ['class' => $this->class['container']]);
        return $this;
    }

    public function containerFull($close, $content = null)
    {
        $this->markup('div', $close, $content, ['class' => $this->class['container-fluid']]);
        return $this;
    }

    public function but($close, $content = "", $options = [])
    {

        if (isset($this->class['button'])) {
            $options['class'] = $this->class['button'];
        }

        if (!isset($options['type']))
            $options['type'] = 'default';

        if (!isset($options['href']))
            $options['href'] =  "Javascript:;";


        return $this->markup('a', $close, $content, $options);
    }

    function addHead($options)
    {
        $this->head = "<head>";
        $this->head .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
        if (!empty($options['desciption']))
            $this->head .= "<meta name=\"description\" content=\"" . $options['desciption'] . "\" />";
        if (!empty($options['keywords']))
            $this->head .= "<meta name=\"keywords\" content=\"" . $options['keywords'] . "\" />";
        if (!empty($options['title']))
            $this->head .= "<title>" . $options['title'] . "</title>";
        if (!empty($options['favicon']))
            $this->head .= "<link rel=\"icon\" type=\"image/png\" href=\"" . _PUBLIC_DIR . $options['favicon'] . "\" />";
        if (!empty($options['style']))
            $this->head .= "\n" . $options['style'];
        if (!empty($options['script']))
            $this->head .= $options['script'];
        $this->head .= $options['others'];
        $this->head .= "<meta name='Author' content='" . $options['author'] . "' />";
        $this->head .= "<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1'>";

        $this->head .= "</head>{$this->newline}";

        return $this;
    }
}
