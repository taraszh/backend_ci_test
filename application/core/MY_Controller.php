<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    const HTTP_OK = 200;
    const HTTP_BAD = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_SERVER_ERROR = 500;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Frontend send post data as php://input, so transfer it to $_POST, to be able to use validation rules
     *
     * @return void
     */
    protected function fill_global_post_with_input_stream(): void
    {
        $_POST = json_decode($this->input->raw_input_stream, true);
    }

    public function __destruct()
    {

    }
}