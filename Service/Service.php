<?php

namespace oddsCalculator\Service;

use oddsCalculator\Config as Config;

class Service {

    /**
     * Calling with CURL the main API
     * @param $url
     * @return mixed|string
     */
    private static function getData($url) {

        $curl = curl_init();
        $apiKey = Config::$API_KEY;
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "x-rapidapi-host: odds.p.rapidapi.com",
                "x-rapidapi-key: $apiKey"
            ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        if ($err) {
            //TODO: here i should handle this with an exception. 
        }
        curl_close($curl);
        return $response;
    }

    /**
     * @return mixed|string
     */
    public static function getOdds() {
        //TODO: fractal method is currently missing... didn't have time but 
        // it will be easy to implement. 
        
        global $wpdb;
        $odds = [];
        parse_str($_GET['odds_data'], $odds);
        $odds_format = $_GET['odds_format'];
        $totCount = count($odds['odds']);
        $oddsCalculated = [];
        $time = time();
        $american = $decimal = $fractal = false;
        switch ($odds_format) {
            case 'american':
                $american = true;
                break;
            case 'decimal':
                $decimal = true;
                break;
            case 'fractal':
                $fractal = true;
                break;
            default :
                $decimal = true;
                break;
        }
        $stakesSave = [];
        $oddsSave = [];
        for ($i = 0; $i < $totCount; $i++) {
            $stake = $odds['stake'][$i];
            $odd = $odds['odds'][$i];
            if(empty($stake) || empty($odd)) {
                return;
            }
            $oddsSave[] = [$stake,$odd];
            if ($decimal) {
                $oddsCalculated[] = $odd * $stake;
            }
            if ($american) {
                if ($odd < 0) {
                    $oddsCalculated[] = round((100 / $odd) * $stake, 2);
                } else {
                    $oddsCalculated[] = round($odd * ($stake / 100), 2);
                }
            }
        }
        
        $payout = array_sum($oddsCalculated);
        
        $currency = Config::$CURRENCY;
        $aMsg = ['status' => 'ok', 'msg' => "$currency $payout"];
        
        self::saveOdds($oddsSave, $totOddsCalculated, $odds_format,$payout);
        echo json_encode($aMsg);
        wp_die();
    }
    
    private static function saveOdds($oddsSave, $totOddsCalculated, $odds_format, $payout){
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "INSERT INTO `wp_odds_latest` (`updated_at`, `format`, `total_payout`)
            VALUES (now(), %s, %f);", $odds_format, $payout));
        $lastInsertId = $wpdb->insert_id;
        foreach($oddsSave as $oddSave) {
            $odd = $oddSave[0];
            $stake = $oddSave[1];
            $wpdb->query($wpdb->prepare(
                "INSERT INTO `wp_odds_latest_details` (`odds_id`, `odds`, `stake`)
                VALUES (%f, %f, %f);",$lastInsertId, $odd, $stake));
        }
    }
    
    /**
     * To avoid calling several times the remote API i store the data in the DB
     * We can force update with the variable $force_update
     * 
     * @param type $aOptions
     * @param type $force_update
     * @return type
     */
    public static function getRemote($force_update = false) {
        $type = !empty($_GET['type']) ? $_GET['type'] : false;
        $data = $aTmp = [];
        switch($type) {
            case "odds":
                
                $region = !empty($_GET['region']) ? filter_input(INPUT_GET, 'region', FILTER_SANITIZE_SPECIAL_CHARS) : false;
                $sport = !empty($_GET['sport']) ? filter_input(INPUT_GET, 'sport', FILTER_SANITIZE_SPECIAL_CHARS) : false;
                $mkt = !empty($_GET['mkt']) ? filter_input(INPUT_GET, 'mkt', FILTER_SANITIZE_SPECIAL_CHARS) : false;
                
                if (empty($region) || empty($sport) || empty($mkt) ) {
                    echo json_encode(["status" => "error","msg"=> "You need to specify region / sport / mkt"]);
                }
                $option_name = "odds_calculator_odds_".$sport."_".$region."_"."$mkt";
                if (empty(get_option($option_name)) || !empty($force_update)) {
                    $remoteUrl = Config::$ODDS_URL;
                    $remoteUrl = str_replace("{sport}",$sport,$remoteUrl);
                    $remoteUrl = str_replace("{region}",$region,$remoteUrl);
                    $remoteUrl = str_replace("{mkt}",$mkt,$remoteUrl);
                    $data = self::getData($remoteUrl);
                    update_option($option_name, $data);
                } else {
                    $data = get_option($option_name);
                }
                $data = json_decode($data);
                foreach($data->data as $d) {
                    foreach($d->sites as $site) {
                        switch($mkt) {
                            case 'h2h':
                                $odds1 = $site->odds->h2h[0];
                                $odds2 = $site->odds->h2h[1];
                                break;
                            case 'spreads':
                                $odds1 = $site->odds->spreads->odds[0];
                                $odds2 = $site->odds->spreads->odds[1];
                                break;
                            case 'totals':
                                $odds1 = $site->odds->totals->odds[0];
                                $odds2 = $site->odds->totals->odds[1];
                                
                                break;
                        }
                        $aTmp[] = [
                            "sport_nice" => $d->sport_nice,
                            "teams1" => $d->teams[0],
                            "teams2" => $d->teams[1],
                            "site_nice" => $site->site_nice,
                            "odds1" => $odds1,
                            "odds2" => $odds2
                        ];
                    }
                }
                $aData['data'] = $aTmp;
                $data = json_encode($aData);
                break;
            case "sport":
                    $remoteUrl = Config::$SPORTS_URL;
                    if (empty(get_option('odds_calculator_sports')) || !empty($force_update)) {
                        $data = self::getData($remoteUrl);
                        update_option("odds_calculator_sports", $data);
                    } else {
                        $data = get_option('odds_calculator_sports');
                    }
                $sports = json_decode($data);
                foreach ($sports->data as $data) {
                    $aTmp[$data->key] = $data->title;
                }
                $data = json_encode($aTmp);
                break;
        }
        echo $data;
        wp_die();
    }

    /**
     * 
     * @param type $type
     */
    public function getTemplate($type) {
        switch ($type) {
            case "sports":
                $this->includeTemplate("odds_choice");
                break;
            case "odds":
                $this->includeTemplate("odds_calculator"); 
                break;
            case "latest":
                $this->includeTemplate("odds_latest"); 
                break;
        }
    }

    /**
     * Check if the corresponding template file exists otherwise use the default one. 
     * @param type $file
     */
    public function includeTemplate($file) {
        if (file_exists(DIRECTORY_SEPARATOR . $file)) {
            include_once(DIRECTORY_SEPARATOR . $file);
        } else {
            include_once(dirname(__FILE__) . "/../templates/{$defaultFolder}{$file}.php");
        }
    }

    /**
     * 
     * @return type
     */
    public function getChoices(){
        $type = $_GET['type'];
        switch ($type) {
            case 'region':
                echo json_encode(Config::$REGION);
                break;
            case 'mkt':
                echo json_encode(Config::$MKT);
                break;
            case 'sport':
                $sports = $this->getRemote("sport");
                break;
        }
        wp_die();
        
    }
    /**
     * @param type $aOptions
     * @param type $force_update
     * @return type
     */
    public static function getLatest() {
        global $wpdb;
        
        //TODO: i didn't put any limit here... for testing purposes. 
        // but takes few min to add and test in case.
        $sql = "SELECT * FROM
                    wp_odds_latest
                JOIN
                    wp_odds_latest_details
                ON 
                    wp_odds_latest.id = wp_odds_latest_details.odds_id";
        $data = $wpdb->get_results($sql);
        echo json_encode($data);
        wp_die();
    }

}
