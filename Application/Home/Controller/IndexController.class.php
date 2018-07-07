<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller {

    public function test() {
        $user = M('user');
        $data = $user->select();
        $this->ajaxReturn($data, 'json');
    }
}