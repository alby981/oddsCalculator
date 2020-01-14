<?php

namespace oddsCalculator;


class Oddslatest {
    /**
     * Here i should throw exceptions in case the table creation goes bad. 
     * For speed and testing purposes i skipped for now. 
     * 
     * @global type $wpdb
     * @return $this
     */
    static function createTable(){
        global $wpdb;
        $sql = "CREATE TABLE `wp_odds_latest` (
                            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                             `updated_at` timestamp NOT NULL DEFAULT current_timestamp,
                             `format` varchar(255) NOT NULL,
                             `total_payout` float NULL
                        );";
        $wpdb->query($sql);
        
        $sql = "CREATE TABLE `wp_odds_latest_details` (
                    `odds_id` int NOT NULL,
                     `odds` float NULL,
                     `stake` float NULL
                );";
        
        $wpdb->query($sql);
    }

}