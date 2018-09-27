<?php

namespace Home\Controller;
use Think\Controller;
use Think\Exception;
use Think\Upload;

class IndexController extends Controller {
    


    public function index() {
        echo 'index';
    }

    // 用户首次登录
    public function login() {
        $code = I('code');

        $appid = 'wx2239c83348d61f24';
        $secret = 'd7d5c1e78bb42682890b9ba40f87ecfa';
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';

        // get openid and session_key
        //$data = file_get_contents($url);
        $data = $this->http_request($url);

        $jsonData = json_decode($data);
        $arr = get_object_vars($jsonData);
        $openid = $arr['openid'];
        $session_key = $arr['session_key'];

        $user = M('user');
        $res = $user->where("openid='%s'", $openid)->find();
        if ($res) {
            echo $openid;
        } else {
            $user->openid = $openid;
            $user->session_key = $session_key;
            $res1 = $user->add();
            if ($res1) {
                echo $openid;
            } else {
                echo '0'; // 用户openid存入数据库失败
            }
        }

    }

    // HTTP请求（支持HTTP/HTTPS，支持GET/POST）
    private function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (! empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    // 保存用户名
    public function addUserInfo() {
        $openid = $_POST['openid'];
        $username = $_POST['username'];
        $user = M('user');
        $user->username = $username;
        $res = $user->where("openid='%s'", $openid)->save();
        if ($res) {
            echo '1';
        } else {
            echo '0';
        }
    }

    // 获取场馆列表
    public function getVenueList() {
        $page = I('page');
        $venue = M('venue');

        $count = $venue->count();
        // pageNum为页数，10条数据一页
        $pageNum = (int) ceil($count/10);

        $data = $venue->limit($page*10, 10)->select();

        foreach ($data as &$item) {
            $vid = $item['id'];
            $vs = M('venue_student');
            $isBind = 0;
            // 判断学员是否绑定过该场馆
            $data1 = $vs->where('vid=%d AND state=1', $vid)->count();
            if ($data1>0) {
                $isBind = 1;
            }
            $item['isBind'] = $isBind;
        }

        $newData = array();
        $newData['num'] = $pageNum;
        $newData['list'] = $data;

        //array_unshift($data, array('nums'=>$pageNum));
        //dump($data);
        $this->ajaxReturn($newData, 'json');
    }

    // 按id查找场馆
    public function getVenueById() {
        $id = I('id');
        $venue = M('venue');
        $data = $venue->where('id=%d', $id)->find();
        $this->ajaxReturn($data, 'json');
    }

    // 按name查找场馆（可能不止一个）
    public function getVenueByName() {
        $name = $_POST['name'];
        $venue = M('venue');
        $data = $venue->where("name = '%s'", $name)->select();
        $this->ajaxReturn($data, 'json');
    }

    // 按id或name查找场馆
    public function getVenue() {
        $id = $_POST['id'];
        $name = $_POST['name'];

        $venue = M('venue');

        $data = '';

        if ($id) {
            $data = $venue->where('id=%d', $id)->select();
        }

        if ($name) {
            $where['name'] = array('like', '%'.$name.'%');
            $data = $venue->where($where)->select();
        }

        $this->ajaxReturn($data, 'json');
    }

    // 首页信息
    public function getHomePage() {
        $id = $_POST['id'];
        $venue = M('venue');
        $data = $venue->where('id=%d', $id)->find();
        $venue_photo = M('venue_photo');
        $banner = $venue_photo->where('vid=%d AND flag=0', $id)->select();
        $photos = $venue_photo->where('vid=%d AND flag=1', $id)->select();
        $data['banner'] = $banner;
        $data['photos'] = $photos;
        /*array_push($data,'banner', $banner);
        array_push($data, 'photos', $photos);*/
        $this->ajaxReturn($data);
    }

    // 场馆入驻
    public function addOneVenue() {
        $name = $_POST['name'];
        $address = $_POST['address'];
        $address_detail = $_POST['address_detail'];
        $info = $_POST['info'];
        $openid = $_POST['openid'];

        $venue = M('venue');
        $venue->name = $name;
        $venue->address = $address;
        $venue->address_detail = $address_detail;
        $venue->info = $info;
        $venue->openid = $openid;
        $vid = $venue->add();

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     113145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =      'Public/Uploads/'; // 设置附件上传根目录
        // 上传图片，封面和图片的key分别为 banner, photo
        $photo   =   $upload->upload();
        if ($photo) {
            $venue_photo = M('venue_photo');
            $len = count($photo);
            for ($i = 0; $i < $len; $i++) {
                $dir =  '/Public/Uploads/' . $photo[$i]['savepath'].$photo[$i]['savename'];
                $venue_photo->vid = $vid;
                $venue_photo->photo = $dir;
                if ($photo[$i]['key'] == 'banner') {
                    $venue_photo->flag = 0;
                } else {
                    $venue_photo->flag = 1;
                }
                $venue_photo->add();
            }

            echo $vid; // 返回场馆id

        } else {
            echo '0'; // 上传图片失败
        }
    }

    // 上传图片测试
    public function uploadPhoto() {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     113145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =      'Public/Uploads/'; // 设置附件上传根目录
        // 上传单个文件
        $banner   =   $upload->uploadOne($_FILES['banner']);
        if ($banner) {
            $dir =  '/Public/Uploads/' . $banner['savepath'].$banner['savename'];
            echo $dir;
        }
    }

    // 上传多张图片测试
    public function uploadPhoto111() {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     113145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =      'Public/Uploads/'; // 设置附件上传根目录
        $photo   =   $upload->upload();
        if ($photo) {
            $len = count($photo);
            for ($i = 0; $i < $len; $i++) {
                $dir = '/Public/Uploads/' . $photo[$i]['savepath'] . $photo[$i]['savename'];
                echo $dir . '<br>';
                if ($photo[$i]['key'] == 'banner') {
                    echo '1<br>';
                }
            }

        }
    }

    // 学员申请绑定场馆
    public function stuApplyForVenue() {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $openid = $_POST['openid'];
        $vid = $_POST['vid'];
        $flag = $_POST['flag'];

        $user = M('user');
        $user->name = $name;
        $user->phone = $phone;
        $res = $user->where("openid='%s'", $openid)->save();

        $venue_student = M('venue_student');
        $venue_student->vid = $vid;
        $venue_student->openid = $openid;
        $venue_student->state = 0;
        $venue_student->flag = $flag;
        $venue_student->date = date('Y.m.d');
        $venue_student->time = date("H:i:s");
        $res = $venue_student->add();

        if ($res) {
            echo $res; // 返回记录id
        } else {
            echo '0';
        }
    }

    // 处理学员绑定场馆
    public function dealStuApplyForVenue() {
        $id = $_POST['id'];
        $openid = $_POST['openid'];
        $vid = $_POST['vid'];
        $state = $_POST['state'];

        $vs = M('venue_student');
        $vs->state = $state;
        $vs->where('id=%d', $id)->save();



        // 若同意，则将该用户加入student表
        if ($state == 1) {

            // 记录日志
            $date = date('Y.m.d');
            $time = date("H:i:s");
            // 查找用户名字
            $name = M('user')->where("openid='%s'", $openid)->getField('name');
            $event = $name . ' 成为馆内学员';
            $log = M('log');
            $log->event = $event;
            $log->date = $date;
            $log->time = $time;
            $log->add();


            $stu = M("student");
            $stu->openid = $openid;
            $stu->energy = 0;
            $stu->vid = $vid;
            $stu->date = $date;
            $stu->time = $time;
            $res = $stu->add();
            if ($res) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

    // 获取消息列表（学员申请绑定场馆）
    public function getStuApplyList() {
        $vid = $_POST['vid'];
        $vs = M("venue_student");
        $data = $vs->where('vid=%d', $vid)->select();
        foreach ($data as &$item) {
            $name = M('user')->where("openid='%s'", $item['openid'])->getField('name');
            $item['event'] = $name . '申请成为馆内学员';
        }
        $this->ajaxReturn($data, 'json');
    }

    // 日志列表
    public function getLogList() {
        $vid = $_POST['vid'];
        $log = M('log');
        $data = $log->where('vid=%d', $vid)->select();
        $this->ajaxReturn($data, 'json');
    }

    public function test() {
        $user = M('user');
        $data = $user->select();
        foreach ($data as &$item) {
            $item['event'] = '111';
        }
        $this->ajaxReturn($data, 'json');
    }
}