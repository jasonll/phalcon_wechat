<?php
namespace Controllers;

class IndexController extends WxBase 
{
	public function indexAction()
	{
		$text = 'index index';
		$this->view->text = $text;
		$this->render_view('test', 'index');
	}

}
     