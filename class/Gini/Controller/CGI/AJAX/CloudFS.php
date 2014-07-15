<?php
/**
* @file CloudFS.php
* @brief ajax通用调用，所有cloud中需要同意实现的异步请求处理放在这里
* @author PiHiZi
* @version 0.1.0
* @date 2014-07-11
 */

namespace Gini\Controller\CGI\AJAX;

class CloudFS extends \Gini\Controller\CGI
{
    final public function showJSON($data)
    {
        return \Gini\IoC::construct('\Gini\CGI\Response\JSON', $data);
    }

    public function actionGetConfig()
    {
        $form = $this->form();
        $cloud = $form['cloud'];
        $cfs = \Gini\IoC::construct('\Gini\CloudFS', $cloud);
        $config = $cfs->getUploadConfig();
        return $this->showJSON($config);
    }
}