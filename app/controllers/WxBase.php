<?php
namespace Controllers;

/**
 * [WxBase 微信认证登录base]
 * 
 */
class WxBase extends ControllerBase 
{
	protected $wx_user;

    public function initialize()
    {
        $this->wxoauth();
        $this->wx_user = $this->session->get('wx_user');
    }

    /**
     * [render_view description]
     * @Author   Jason
     * @DateTime 2016-12-13T12:16:36+0800
     * @param    [type]                   $path      [description]
     * @param    [type]                   $view      [description]
     * @param    [type]                   $js_config [微信jssdk 配置]
     * @return   [type]                              [description]
     */
    protected function render_view($path, $view, $js_config = array())
    {
        $default_config = [];
        $js_config = array_merge( $default_config, $js_config );

        $this->view->jsconfig = $this->wxapp->js->config( $js_config, $this->config->debug, false, true );
        parent::render_view($path, $view);
    }

    /**
     * [wxoauth 微信登录认证]
     * @Author   Jason
     * @DateTime 2016-12-13T11:02:28+0800
     * @return   [type]                   [description]
     */
    private function wxoauth()
    {
        if ( !$this->session->has('wx_user') ) {
            if ( $this->request->has('state') && $this->request->has('code') ) {
                $this->session->set('wx_user', $this->wxapp->oauth->user());
                return $this->response->redirect( $this->session->get('target_url') )->send();
            }

            $this->session->set('target_url', $this->request->getURI());

            $scopes = $this->config->wechat->scopes;

            if (is_string($scopes)) {
                $scopes = array_map('trim', explode(',', $scopes));
            }

            return $this->wxapp->oauth->scopes( $scopes )->redirect()->send();
        }   
    }
}
     