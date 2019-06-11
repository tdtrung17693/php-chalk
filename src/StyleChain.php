<?php
/**
 * Exception TdTrung\Chalk
 *
 * @package TdTrung\Chalk
 * @author  Tran Dinh Trung <trandinhtrung176@gmail.com>
 */

namespace TdTrung\Chalk;

class StyleChain
{
    public $styles = [];
    private $colorInstance;

    public function __construct($style, Chalk $colorInstance)
    {
        array_push($this->styles, $style);
        $this->colorInstance = $colorInstance;
    }

    public function __invoke()
    {
        $arguments = func_get_args();

        array_unshift($arguments, $this->styles);

        return call_user_func_array(
            [$this->colorInstance, 'apply'],

            $arguments
        );
    }

    public function __get($prop)
    {
        $other = $this->colorInstance->{$prop};
        $this->merge($other);

        return $this;
    }

    public function __call($method, $arguments)
    {

        if ($this->colorInstance->isTwoStageFns($method)) {
            $result = call_user_func_array(
                [$this->colorInstance, $method],
                $arguments
            );
            $this->merge($result);
            return $this;
        }

        $other = $this->colorInstance->{$method};
        $this->merge($other);

        return call_user_func_array([$this, '__invoke'], $arguments);
    }

    private function merge(StyleChain $other)
    {
        $this->styles = array_merge($this->styles, $other->styles);
    }
}
