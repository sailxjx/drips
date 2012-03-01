<?php
$objHotMap = new HotBaiduMap();
$arrAttrs = $objHotMap->main();

if (!empty($arrAttrs)) {
    extract($arrAttrs);
}

class HotBaiduMap {

    const PARAM_KEY_AJAX = 'ajax';
    const PARAM_KEY_IMG = 'img';

    protected static $intWidth = 1000;
    protected static $intHeight = 600;
    protected static $strCity;
    protected $intCityId;
    protected $strSqlGetId = 'SELECT id FROM e_property WHERE city_id=?';
    protected $strSqlGetPoint = 'SELECT id,lng,lat FROM e_map_property WHERE id IN (?)';
    protected $strSqlGetPrice = 'SELECT loupan_id,mid_price_office_sale FROM dw_jp_midprice_monthly WHERE month_id="2011M11" AND !ISNULL(mid_price_office_sale) AND loupan_id IN (?)';

    public function main() {
        $this->intCityId = !empty($_REQUEST['cityid']) ? $_REQUEST['cityid'] : 11;
        $this->getCity();
        $this->set_attribute('arrData', $this->getMapData());
        $this->set_attribute('strCity', self::$strCity);
        $this->set_attribute('cityid', $this->intCityId);
        $this->set_attribute('arrRangeTip', $this->getRangeTip());
        return $this->attributes;
    }

    protected function set_attribute($key, $value) {
        $this->attributes[$key] = $value;
    }

    protected $attributes;

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

    protected function hotMap() {
        $objApf = APF::get_instance();
        $objReq = $objApf->get_request();
        $objRes = $objApf->get_response();
        return 'Try_HotMap';
    }

    protected function getMapData() {
//        $this->arrIds = $this->getIds();
//        $arrPoints = $this->getPoint();
//        $arrPrice = $this->getPrice();

        include './data/Data.dic';
        $this->arrIds = $ids;
        $arrPoints = $points;
        $arrPrice = $prices;

        $arrData = array();
        $arrIds = array();
        foreach ($arrPoints as $key => $value) {
            $arrData[$value['id']] = array(
                'lng' => $value['lng'],
                'lat' => $value['lat']
            );
        }
        usort($arrPrice, 'self::sortById');
        $intCnt = count($arrPrice);
        for ($i = 0; $i < $intCnt; $i++) {
            if (!empty($arrPrice[$i]['mid_price_office_sale'])) {
                $this->intMin = intval($arrPrice[$i]['mid_price_office_sale']);
                break;
            }
            unset($arrPrice[$i]);
        }
        $this->intMax = isset($arrPrice[$intCnt - 1]['mid_price_office_sale']) ? intval($arrPrice[$intCnt - 1]['mid_price_office_sale']) : 0;
        $arrPrice = $this->formatColor($arrPrice);
        $arrDataFinal = array();
        foreach ($arrPrice as $key => $valAPrice) {
            if (isset($arrData[$valAPrice['loupan_id']])) {
                $arrDataFinal[] = array(
                    $arrData[$valAPrice['loupan_id']],
                    $valAPrice['c']
                );
            }
        }
        return $arrDataFinal;
    }

    public static function sortById($a, $b) {
        return $a['mid_price_office_sale'] > $b['mid_price_office_sale'] ? 1 : 0;
    }

    protected $arrIds = array();

    protected function getIds() {
        $objDbFac = APF_DB_Factory::get_instance()->get_pdo('jinpu_db_slave');
        $objStmt = $objDbFac->prepare($this->strSqlGetId);
        $objStmt->execute(array($this->intCityId));
        return $objStmt->fetchAll(PDO::FETCH_COLUMN);
    }

    protected function getPoint() {
        $objDbFac = APF_DB_Factory::get_instance()->get_pdo('jinpu_db_slave');
        $strSql = str_replace('?', implode(',', $this->arrIds), $this->strSqlGetPoint);
        $objStmt = $objDbFac->prepare($strSql);
        $objStmt->execute();
        return $objStmt->fetchAll();
    }

    protected function getPrice() {
        $objDbFac = APF_DB_Factory::get_instance()->get_pdo('jinpu_stats_slave');
        $strSql = str_replace('?', implode(',', $this->arrIds), $this->strSqlGetPrice);
        $objStmt = $objDbFac->prepare($strSql);
        $objStmt->execute();
        return $objStmt->fetchAll();
    }

    public function buildUri() {
        return '/try/hotmap';
    }

    public $arrPrice;
    public $intMax = 20000;
    public $intMin = 6000;
    public $floAlpha = 0.8;
    public $arrColorRange = array();
    public $intRange = 6;

    public function formatColor($arrPrice) {
        $this->colorStep();
        $intStep = ($this->intMax - $this->intMin) / ($this->intRange + 1);
        $arrRange = range($this->intMin, $this->intMax, $intStep);
        foreach ($arrRange as $key => $value) {
            $arrRange[$key] = intval($value);
        }
        while (count($arrRange) > ($this->intRange + 1)) {
            unset($arrRange[count($arrRange) - 1]); //delete the last ele of the range;
        }
        $arrColorRange = array_combine($arrRange, array_reverse($this->getColorRange($this->intRange)));
        foreach ($arrPrice as $keyAPrice => $valAPrice) {
            foreach ($arrRange as $keyARange => $valARange) {
                if ($valAPrice['mid_price_office_sale'] >= $valARange && (!isset($arrRange[$keyARange + 1]) || $arrRange[$keyARange + 1] > $valAPrice['mid_price_office_sale'])) {
                    $arrPrice[$keyAPrice]['c'] = $this->arrColorRange[$arrColorRange[$valARange]];
                }
            }
        }
        return $arrPrice;
    }

    public function getRangeTip() {
        $intStep = ($this->intMax - $this->intMin) / ($this->intRange + 1);
        $arrRange = range($this->intMin, $this->intMax, $intStep);
        $arrRangeTip = array();
        $arrColorRange = array_reverse(array_values($this->arrColorRange));
        foreach ($arrColorRange as $key => $valACRange) {
            $arrRangeTip[$key] = $valACRange;
            $intMax = isset($arrRange[$key + 1]) ? intval($arrRange[$key + 1]) : intval($this->intMax);
            $arrRangeTip[$key]['t'] = intval($arrRange[$key]) . '-' . $intMax;
        }
        return array_reverse($arrRangeTip);
    }

    public function colorStep() {
        $arrColor = $this->getColorRange($this->intRange);
        foreach ($arrColor as $key => $deg) {
            if ($deg < 90) {
                $this->arrColorRange[$deg] = array(intval(255 * cos(deg2rad($deg))), intval(255 * sin(deg2rad($deg))), 0);
            } else {
                $dega = 180 - $deg;
                $this->arrColorRange[$deg] = array(0, intval(255 * sin(deg2rad($dega))), intval(255 * cos(deg2rad($dega))));
            }
        }
        return $this->arrColorRange;
    }

    public function getColorRange($intRange) {
        $intStep = intval(180 / $intRange);
        $arrColor = range(0, 180, $intStep);
        return $arrColor;
    }

}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>地图</title>
        <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
        <link rel="stylesheet" rev="stylesheet" href="./css/HotMap.css" type="text/css" />
        <script type="text/javascript" src="./js/jquery-1.6.min.js"></script>
    </head>
    <body>
        <div id="content">
            <h1 id="tip" style="display: none;">LOADING...</h1>
            <div id="map"></div>
            <input type="button" id="save" value="保存图片"/>
            <input type="hidden" id="cityname" value="<?= $strCity ?>"/>
            <div class="clean"/>
            <img src="" id="merge_pic" style="display: none;"/>
        </div>
        <script type="text/javascript" src="http://api.map.baidu.com/api?v=1.2&services=true"></script>
        <script type="text/javascript">
            var data=<?= isset($arrData) ? json_encode($arrData) : null; ?>;
            var cityid="<?= isset($cityid) ? $cityid : null ?>";
            var colorRange=<?= json_encode($arrRangeTip); ?>;
        </script>
        <script type="text/javascript" src="./js/HotMap.js"></script>
    </body>
</html>