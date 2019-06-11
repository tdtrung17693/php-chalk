<?php
/**
 * Exception TdTrung\Chalk\InvalidStyleException
 *
 * @author Tran Dinh Trung <trandinhtrung176@gmail.com>
 * @package TdTrung\Chalk
 */
namespace TdTrung\Chalk;

class InvalidStyleException extends \Exception
{
    /**
     * InvalidStyleExceiption constructor.
     * @param string $style Style name
     */
    public function __construct($style)
    {
        parent::__construct("Invalid style {$style}.");
    }
}
