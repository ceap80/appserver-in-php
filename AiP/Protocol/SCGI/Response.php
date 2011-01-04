<?php
namespace AiP\Protocol\SCGI;

class Response
{
    private static $valid_statuses = null;

    private $scgi = null;

    private $headers = array();
    private $sent_headers = false;

    private $content_type = null;
    private $status = 200;

    public function __construct(Server $scgi)
    {
        if (null === self::$valid_statuses) {
            self::$valid_statuses = array(
                100 => "Continue",
                101 => "Switching Protocols",
                102 => "Processing",
                200 => "OK",
                201 => "Created",
                202 => "Accepted",
                203 => "Non-Authoritative Information",
                204 => "No Content",
                205 => "Reset Content",
                206 => "Partial Content",
                207 => "Multi-Status",
                300 => "Multiple Choices",
                301 => "Moved Permanently",
                302 => "Found",
                303 => "See Other",
                304 => "Not Modified",
                305 => "Use Proxy",
                306 => "Switch Proxy",
                307 => "Temporary Redirect",
                400 => "Bad Request",
                401 => "Unauthorized",
                402 => "Payment Required",
                403 => "Forbidden",
                404 => "Not Found",
                405 => "Method Not Allowed",
                406 => "Not Acceptable",
                407 => "Proxy Authentication Required",
                408 => "Request Timeout",
                409 => "Conflict",
                410 => "Gone",
                411 => "Length Required",
                412 => "Precondition Failed",
                413 => "Request Entity Too Large",
                414 => "Request-URI Too Long",
                415 => "Unsupported Media Type",
                416 => "Requested Range Not Satisfiable",
                417 => "Expectation Failed",
                418 => "I'm a teapot",
                422 => "Unprocessable Entity",
                423 => "Locked",
                424 => "Failed Dependency",
                425 => "Unordered Collection",
                426 => "Upgrade Required",
                449 => "Retry With",
                450 => "Blocked by Windows Parental Controls",
                500 => "Internal Server Error",
                501 => "Not Implemented",
                502 => "Bad Gateway",
                503 => "Service Unavailable",
                504 => "Gateway Timeout",
                505 => "HTTP Version Not Supported",
                506 => "Variant Also Negotiates",
                507 => "Insufficient Storage",
                509 => "Bandwidth Limit Exceeded",
                510 => "Not Extended",
            );
        }

        $this->scgi = $scgi;

        $this->content_type = ini_get('default_mimetype');

        if ($charset = ini_get('default_charset')) {
            $this->content_type .= '; charset='.$charset;
        }
    }

    public function setStatus($status)
    {
        if (!array_key_exists($status, self::$valid_statuses))
            throw new UnexpectedValueException('Unknown status: '.$status);

        $this->status = $status;
    }

    public function addHeader($name, $value)
    {
        if ($name == 'Content-type') {
            $this->content_type = $value;
        } else {
            $this->headers[] = $name.': '.$value;
        }
    }

    public function sendHeaders()
    {
        $this->scgi->write('Status: '.$this->status.' '.self::$valid_statuses[$this->status]."\r\n");
        $this->scgi->write('Content-type: '.$this->content_type."\r\n");
        $this->scgi->write(implode("\r\n", $this->headers));
        $this->scgi->write("\r\n\r\n");
    }
}