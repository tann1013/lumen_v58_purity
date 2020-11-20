<?php

namespace App\Libraries;

use App\ConstDir\BaseConst;
use Laravel\Lumen\Http\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Arrayable;

class WmApiResponse extends ResponseFactory
{

    public $code = BaseConst::SUCCESS_CODE;
    public $msg = BaseConst::SUCCESS_CODE_MSG;
    public $page = [];

    /**
     * 设置code
     * @param int    $code
     * @param string $msg
     * @return $this
     */
    public function code($code = BaseConst::SUCCESS_CODE, $msg = BaseConst::SUCCESS_CODE_MSG)
    {
        $this->code = $code;
        $this->msg = $msg;
        return $this;
    }

    /**
     * @param $page
     * @return $this
     */
    public function page($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Return a new JSON response from the application. (为格式化返回值改写)
     *
     * @param  string|array $data
     * @param  int          $status
     * @param  array        $headers
     * @param  int          $options
     * @return \Illuminate\Http\JsonResponse;
     */
    public function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        // empty data change to object
        // $data = empty($data) ? new \stdClass() : $data;

        $result = [
            'Code'    => $this->code,
            'Msg'     => $this->msg,
            'NowTime' => time(),
            'Data'    => $data
        ];

        if (!empty($this->page)) {
            $result = array_merge($result, (array)$this->page);
        }

        return new JsonResponse($result, $status, $headers, $options);
    }
}