<?php

$objMergeMap = new MergeMap();
$objMergeMap->main();

class MergeMap {

    protected $strCover;
    protected static $intWidth = 1000;
    protected static $intHeight = 600;
    protected static $strCity;
    protected $intCityId;
    protected static $strImgDir = '../hotmap/pic/';
    protected $strMapName;
    protected $strCoverName;
    protected $strFileName;

    public function main() {
        $this->strCover = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
        $this->intCityId = 11;
        $this->getCity();
        if (!empty($this->strCover)) {
            return $this->ajax();
        }
        return false;
    }

    protected function getCity() {
        self::$strCity = '上海';
        return self::$strCity;
//        $arrCitys = APF::get_instance()->get_config('name', 'multicity');
//        if (isset($arrCitys[$this->intCityId])) {
//            self::$strCity = $arrCitys[$this->intCityId];
//        } else {
//            $this->intCityId = 11;
//            self::$strCity = $arrCitys[$this->intCityId];
//        }
//        return self::$strCity;
    }

    protected function ajax() {
        $strMap = $this->getMap();
        $strMapHash = md5($strMap);
        $this->strMapName = self::$strImgDir . $strMapHash . '.png';
        if (!file_exists($this->strMapName)) {
            file_put_contents($this->strMapName, $strMap);
        }
        $strImg = base64_decode(substr($this->strCover, strpos($this->strCover, ',') + 1));
        $strImgHash = md5($strImg);
        $this->strCoverName = self::$strImgDir . $strImgHash . '.png';
        if (!file_exists($this->strCoverName)) {
            file_put_contents($this->strCoverName, $strImg);
        }
        return $this->mergeImg();
    }

    protected function mergeImg() {
        $imgCover = imagecreatefrompng($this->strCoverName);
        $imgMap = imagecreatefrompng($this->strMapName);
        $imgMerge = imagecreatetruecolor(self::$intWidth, self::$intHeight);
        imagecopy($imgMerge, $imgMap, 0, 0, 0, 0, self::$intWidth, self::$intHeight);
        imagedestroy($imgMap);
        imagecopyresized($imgMerge, $imgCover, 0, 0, 0, 0, self::$intWidth, self::$intHeight, self::$intWidth, self::$intHeight);
        header("Content-type: image/png");
        $this->strFileName = self::$strImgDir . self::$strCity . date('Y-m-d') . '.png';
        imagepng($imgMerge, $this->strFileName);
        return $this->callbackFileName();
    }

    protected function callbackFileName() {
        if (!isset($this->strFileName)) {
            return false;
        }
        return $this->strFileName;
    }

    protected function getMap() {
        $objCurl = new APF_Http_Client_Curl();
        $strUrl = 'http://api.map.baidu.com/staticimage?width=' . self::$intWidth . '&height=' . self::$intHeight . '&center=' . self::$strCity . '&zoom=11';
        $objCurl->set_url($strUrl);
        $objCurl->execute();
        return $objCurl->get_response_text();
    }

    public static function buildUri() {
        return '/try/mergemap';
    }

}

class APF_Http_Client_Curl {

    private $curl;

    /**
     * @return APF_Http_Client_Curl
     */
    public function __construct() {
        $this->curl = curl_init();
        $this->init();
    }

    public function init() {
        $this->set_attribute(CURLOPT_HTTPHEADER, array("Content-type:text/xml; charset=utf-8"));
        $this->set_attribute(CURLOPT_RETURNTRANSFER, 1);
        $this->set_attribute(CURLOPT_CONNECTTIMEOUT, 10);
        $this->set_attribute(CURLOPT_TIMEOUT, 10);
    }

    public function set_url($url) {
        curl_setopt($this->curl, CURLOPT_URL, $url);
    }

    public function set_attribute($name, $value) {
        curl_setopt($this->curl, $name, $value);
    }

    public function set_timeout($time) {
        $this->set_attribute(CURLOPT_TIMEOUT, $time);
    }

    /**
     * @return boolean
     */
    public function execute() {
        $this->response_text = curl_exec($this->curl);
        $this->curl_info = curl_getinfo($this->curl);
        if ($this->curl_info['http_code'] == 200) {
            return true;
        } else {
            return false;
        }
    }

    private $response_text;
    private $curl_info;

    public function get_response_text() {
        return $this->response_text;
    }

    public function get_curl_info() {
        return $this->curl_info;
    }

    public function __destruct() {
        curl_close($this->curl);
    }

}

