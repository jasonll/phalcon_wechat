<?php
namespace Controllers;
use Phalcon\Mvc\Controller;

class ControllerBase extends Controller 
{
    protected function render_view($path, $view)
    {
        $this->view->render($path, $view);
    }

    /**
     * [genRet 接口错误码返回]
     * @Author   Jason
     * @DateTime 2016-09-27T16:21:24+0800
     * @param    [type]                   $errcode [description]
     * @param    string                   $errmsg  [description]
     * @param    [type]                   $data    [description]
     * @return   [type]                            [description]
     */
    protected function genRet($errcode, $errmsg = '', $data = null)
    {
        if ( $errcode != 0 ) {
            $this->logger->error(' errcode => ' . $errcode . ', errmsg => ' . $errmsg . ', data => ' . json_encode($data) );
        }

        echo json_encode( array(
            "errcode" => $errcode,
            "errmsg" => $errmsg,
            "data" => $data
        ));die;
    }
}
     