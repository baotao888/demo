<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/12 0012
 * Time: 14:56
 */

namespace ylcore;


class Agreement
{
    /**
     * 对图片路径经行处理
     * @param integer $url
     * @return integer $return
     */
    public function httpAgreement($url) {
        if (substr($url, 0, 5) !== 'http:' && $url != '') {
            $return = "http:" . $url;
        } else {
            $return = $url;
        }
        return $return;
    }
}