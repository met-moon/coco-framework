<?php
/**
 * Created by PhpStorm.
 * User: ttt
 * Date: 2015/11/22
 * Time: 11:20
 */

namespace coco\base;

class ExitException extends \Exception
{
    /**
     * @var int
     */
    public $statusCode;

    /**
     * @param int $status
     * @param null $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($status = 0, $message = null, $code = 0, \Exception $previous = null){
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }
}