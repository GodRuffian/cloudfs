<?php
/**
* @file Qiniu.php
* @brief 七牛
* @author PiHiZi
* @version 0.1.0
* @date 2014-07-11
 */

namespace Gini\Controller\API\CloudFS;

class Qiniu extends \Gini\Controller\API
{
    private function getConfig()
    {
        $sess = $_SESSION['cloudfs.rpc.qiniu.config'];
        return $sess['config'];
    }

    private function getBucketName()
    {
        $sess = $_SESSION['cloudfs.rpc.qiniu.config'];
        return $sess['bucket'];
    }

    public function actionInit($bucket)
    {
        $config = \Gini\Config::get('cloudfs.server');
        foreach ($config as $c) {
            if ($c['driver']==='qiniu' && $c['bucket']===$bucket) {
                $config = $c['options'];
                $_SESSION['cloudfs.rpc.qiniu.config'] = [
                    'bucket'=> $bucket
                    ,'config'=> $config
                ];
                break;
            }
        }
    }

    // 暂时不允许客户端自定义filename
    public function actionGetURI($host, $filename='')
    {
        $filename = \Gini\Util::randPassword() . microtime();
        $result = md5($host.$filename).'.jpg';
        return $result;
    }

    public function actionGetKeys($params)
    {
        require_once(__DIR__.'/../../../../../vendor/qiniu/php-sdk/qiniu/rs.php');

        $bucket = $this->getBucketName();
        $config = $this->getConfig();

        if (!$config || !$bucket) return;

        $config = [ $bucket, $config['accessKey'], $config['secretKey'] ];
        list($bucket, $accessKey, $secretKey) = $config;

        \Qiniu_SetKeys($accessKey, $secretKey);

        $filename = $params['file'];
        $filename = $filename ? "{$bucket}:{$filename}" : $bucket;
        $putPolicy = new \Qiniu_RS_PutPolicy($filename);

        if (isset($params['callbackBody'])) {
            $putPolicy->CallbackBody = $params['callbackBody'];
        }
        if (isset($params['callbackUrl'])) {
            $putPolicy->CallbackUrl = $params['callbackUrl'];
        }

        $token = $putPolicy->Token(null);
        return $token;
    }

    public function actionIsFromQiniuServer($data, $iAccessKey, $encodedData)
    {
        $bucket = $this->getBucketName();
        $config = $this->getConfig();

        if (!$config || !$bucket) return;

        $config = [ $bucket, $config['accessKey'], $config['secretKey'] ];
        list($bucket, $accessKey, $secretKey) = $config;
        
        if ($iAccessKey!==$accessKey) return false;

        $myEData = str_replace(['+', '/'], ['-', '_'], base64_encode(hash_hmac('sha1', $data, $secretKey, true)));
        if ($myEData !== $encodedData) {
            return false;
        }
        return true;
    }
}