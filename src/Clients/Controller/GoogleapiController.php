<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Clients\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
//use gapi;
use Google_Client;
use Google_AnalyticsService;

/* Set your Google Analytics credentials */

//define('ga_account'     ,'seolawyers2012@gmail.com');
//define('ga_password'    ,'9382devilx');
//define('ga_profile_id'  ,'');

class GoogleapiController extends AbstractActionController {
    /* Set your Google Analytics credentials */

    public function indexAction() {

        ########## Google Settings.. Client ID, Client Secret #############
        $google_client_id = '538954842329-s0n51258qhi195ascgn7sccqbsko6u2s.apps.googleusercontent.com';
        $google_client_secret = 'PANzHe2kkHgMyLMWp3uRpEp1';
        $google_redirect_url = 'http://dashboard.speakeasymarketinginc.com/googleapi/updatedone';
        $page_url_prefix = 'https://www.arizdui.com';

        ########## Google analytics Settings.. #############
        $google_analytics_profile_id = 'ga:123456'; //Analytics site Profile ID
        $google_analytics_dimensions = 'ga:landingPagePath,ga:pageTitle'; //no change needed (optional)
        $google_analytics_metrics = 'ga:pageviews'; //no change needed (optional)
        $google_analytics_sort_by = '-ga:pageviews'; //no change needed (optional)
        $google_analytics_max_results = '20'; //no change needed (optional)
       
        //start session
        session_start();
        
        $gClient = new Google_Client();
        $gClient->setApplicationName('Login to saaraan.com');
        $gClient->setClientId($google_client_id);
        $gClient->setClientSecret($google_client_secret);
        $gClient->setRedirectUri($google_redirect_url);
        $gClient->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));
        $gClient->setUseObjects(true);

// echo "enter middle";exit;
//check for session variable
        
       if(!isset($_SESSION["token"])){
                $gClient->authenticate();
                $token = $gClient->getAccessToken();
                $_SESSION["token"] = $token;
       }  
//         print_r($_SESSION);exit;
        if (isset($_SESSION["token"])) {
             print_r("session");exit;
            //set start date to previous month
            $start_date = date("Y-m-d", strtotime("-1 month"));

            //end date as today
            $end_date = date("Y-m-d");

            try {
                //set access token
                $gClient->setAccessToken($_SESSION["token"]);

                //create analytics services object
                $analyticsService = new Google_AnalyticsService($gClient);

                //analytics parameters (check configuration file)
                $params = array('dimensions' => $google_analytics_dimensions, 'sort' => $google_analytics_sort_by, 'filters' => 'ga:medium==organic', 'max-results' => $google_analytics_max_results);

                //get results from google analytics
                $results = $analyticsService->data_ga->get($google_analytics_profile_id, $start_date, $end_date, $google_analytics_metrics, $params);
            } catch (Exception $e) { //do we have an error?
                echo $e->getMessage(); //display error
            }

            $pages = array();
            $rows = $results->rows;

            if ($rows) {
                echo '<ul>';
                foreach ($rows as $row) {
                    //prepare values for db insert
                    $pages[] = '("' . $row[0] . '","' . $row[1] . '",' . $row[2] . ')';

                    //output top page link
                    echo '<li><a href="' . $page_url_prefix . $row[0] . '">' . $row[1] . '</a></li>';
                }
                echo '</ul>';
                exit();
                //empty table
                $mysqli->query("TRUNCATE TABLE google_top_pages");

                //insert all new top pages in the table
                if ($mysqli->query("INSERT INTO google_top_pages (page_uri, page_title, total_views) VALUES " . implode(',', $pages) . "")) {
                    echo '<br />Records updated...';
                } else {
                    echo $mysqli->error;
                }
            }
        } else {
            print_r("auth");exit;
//            //authenticate user
//            if (isset($_GET['code'])) {
//                 print_r("code");exit;
//                $gClient->authenticate();
//                $token = $gClient->getAccessToken();
//                $_SESSION["token"] = $token;
//                header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL));
//            } else {
//                 print_r("no-code");exit;
//                $gClient->authenticate();
//            }
        }
    }

}
