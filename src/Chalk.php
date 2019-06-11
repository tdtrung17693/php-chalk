<?php
namespace TdTrung\Chalk;

use TdTrung\OSRecognizer\OSRecognizer;

class Chalk
{
    const RESET = "\033[0m";
    private $styles = [
        'reset' => 0,
        'bold' => 1,
        'dim' => 2,
        'italic' => 3,
        'underscore' => 4,
        'blink' => 5,
        'inverse' => 7,
        'strikethrough' => 9,
        'black' => 30,
        'red' => 31,
        'green' => 32,
        'yellow' => 33,
        'blue' => 34,
        'magenta' => 35,
        'cyan' => 36,
        'lightGray' => 37,
        'darkGray' => 90,
        'lightRed' => 91,
        'lightGreen' => 92,
        'lightYellow' => 93,
        'lightBlue' => 94,
        'lightMagenta' => 95,
        'lightCyan' => 96,
        'white' => 97
    ];

    private $twoStageFns = ["rgb"];
    private $osRecognizer;
    private $supportLevel = 0;
    private $enableColor = true;

    public function __construct()
    {
        $this->initSeqBuilders();
        $this->osRecognizer = new OSRecognizer;
        $this->checkColorSupport();
    }

    private function initSeqBuilders()
    {
        foreach ($this->styles as $name => $code) {
            $this->styles[$name] = function ($offset) use ($code) {
                if ($code > 0)
                    $code = $offset + $code;
                return "\033[{$code}m";
            };
        }

        $this->styles["rgb"] = function ($r, $g, $b, $offset) {
            // TODO: Fallback to ANSI 256 if possible
            if (!$this->has16mSupport()) return "";

            $type = 38 + $offset;
            return "\033[{$type};2;{$r};{$g};{$b}m";
        };
    }

    private function checkColorSupport()
    {
        if (getenv('TERM') === 'dumb') {
            return 0;
        } else if (strpos($this->osRecognizer->getPlatform(), 'win') !== false) {
            // get os version and build
            $release = explode('.', $this->osRecognizer->getRelease());
            if (intval($release[0]) >= 10 && intval($release[1]) >= 10586) {
                $this->supportLevel = intval($release[2]) >= 14931 ? 3 : 2;
                return;
            }

            $this->supportLevel = 1;
        } else if (strpos(getenv('COLORTERM'), 'truecolor') !== false) {
            $this->supportLevel = 3;
        } else if (function_exists('posix_isatty') && @!posix_isatty(STDOUT)) {
            $this->supportLevel = 1;
        } else if (preg_match('/-256(color)?$/i', getenv('TERM'))) {
            $this->supportLevel = 2;
        } else if (preg_match('/^screen|^xterm|^vt100|^vt220|^rxvt|color|ansi|cygwin|linux/i', getenv('TERM'))) {
            $this->supportLevel = 1;
        } else {
            $this->supportLevel = 0;
        }
    }

    private function is256Color($styleName)
    {
        return preg_match('/^color\d+/i', $styleName);
    }

    private function isValidStyle($styleName)
    {
        if (!(strpos($styleName, 'bg') === false)) {
            preg_match('/^bg(\w+)$/', $styleName, $match);
            $styleName = lcfirst($match[1]);
        }

        return array_key_exists($styleName, $this->styles) || $this->is256Color($styleName);
    }

    private function parseStyleName($styleName)
    {
        $offset = 0;
        if (!(strpos($styleName, 'bg') === false)) {
            $offset = 10;
            preg_match('/^bg(\w+)$/', $styleName, $match);
            $styleName = lcfirst($match[1]);
        }

        return [$offset, $styleName];
    }

    private function get256Sequence($styleName, $offset)
    {
        preg_match('/^color(\d+)/i', $styleName, $match);
        $offset += 38;

        return "\033[{$offset};5;{$match[1]}m";
    }

    public function isTwoStageFns($styleName)
    {
        return array_search($styleName, $this->twoStageFns) !== false;
    }

    public function disableColor()
    {
        $this->enableColor = false;
    }

    public function hasColorSupport()
    {
        return $this->supportLevel >= 1;
    }

    public function has256Support()
    {
        return $this->supportLevel >= 2;
    }

    public function has16mSupport()
    {
        return $this->supportLevel >= 3;
    }

    public function __get($styleName)
    {
        if (!$this->isValidStyle($styleName)) {
            throw new InvalidStyleException($styleName);
        }

        list($offset, $styleName) = $this->parseStyleName($styleName);

        if ($this->is256Color($styleName)) {
            $style = $this->get256Sequence($styleName, $offset);
        } else {
            $style = $this->styles[$styleName]($offset);
        }

        return new StyleChain($style, $this);
    }

    public function __call($styleName, $arguments)
    {
        if (!$this->isValidStyle($styleName)) {
            throw InvalidStyleException($styleName);
        }

        list($offset, $styleName) = $this->parseStyleName($styleName);

        if ($this->isTwoStageFns($styleName)) {
            array_push($arguments, $offset);

            return new StyleChain(
                call_user_func_array($this->styles[$styleName], $arguments),
                $this
            );
        } else if ($this->is256Color($styleName)) {
            $style = $this->get256Sequence($styleName, $offset);
        } else {
            $style = $this->styles[$styleName]($offset);
        }

        array_unshift($arguments, [$style]);

        return call_user_func_array([$this, 'apply'], $arguments);
    }

    public function apply()
    {
        if (func_num_args() < 2) throw new InvalidArgumentException('Insufficient arguments (at least 2 are required)');

        $styles = func_get_arg(0);
        $strings = func_get_args();
        array_shift($strings);
        $text = implode(" ", $strings);

        if (!$this->enableColor || !$this->hasColorSupport()) return $text;

        return array_reduce($styles, function ($carry, $style) {
            return "{$style}{$carry}" . Chalk::RESET;
        }, $text);
    }
}
