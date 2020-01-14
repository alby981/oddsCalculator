<?php

namespace oddsCalculator;

class Config
{
    static $ODDS_URL = 'https://odds.p.rapidapi.com/v1/odds?sport={sport}&region={region}&mkt={mkt}';
    static $SPORTS_URL = 'https://odds.p.rapidapi.com/v1/sports';
    
    //THE FOLLOWING API KEY SHOULD BE IN A WP CONFIG FILE 
    // OR IN THE DB AND SET UP THROUGH AN ADMIN INTERFACE (WP_OPTION)
    static $API_KEY = '';
    static $CURRENCY = '$';
    
    static $REGION = [
        "us" => "United States",
        "uk" => "United Kingdom",
        "au" => "Australia"
    ];
    
    static $MKT = [
        "h2h" => "h2h",
        "spreads" => "spreads",
        "totals" => "totals"
    ];
}