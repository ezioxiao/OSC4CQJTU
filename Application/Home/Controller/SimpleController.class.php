<?php
namespace Home\Controller;
use Think\Controller;
class SimpleController extends Controller {
    /**
     * 空操作处理
     * @return custom or 404 page
     */
    public function _empty() {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
    }
}