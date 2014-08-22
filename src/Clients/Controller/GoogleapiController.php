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
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Clients\Model\Website;
use Clients\Model\WebsiteTable;
use Zend\Session\Container;
use gapi;

class GoogleapiController extends AbstractActionController {

    public function indexAction() {
        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('googleapi');
//            $session->getManager()->getStorage()->clear();
            $session->offsetSet('googleapi_client_id', $id);
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            if ($id == 0) {
                print_r("Cant find Client ID");
                exit;
            }
            //connection with website table
            $tableGatewayWebsite = $this->getConnectionWebsite();
            $websiteTable = new WebsiteTable($tableGatewayWebsite);
            //get all client website id
            $client_websites = $websiteTable->getWebsiteClients($id);
            //get clinet current website id
            foreach ($client_websites as $value) {
                $current_website_id = $value->id;
                break;
            }
            //check if current websute id session is avilable
            if ($session->offsetExists('current_website_id') && $session->offsetGet('current_website_id') != '') {
                $current_website_id = $session->offsetGet('current_website_id');
                //if date range is selected
                if ($session->offsetExists('from') && $session->offsetGet('from') != '') {
//                    echo "google api function";exit;
                    $current_website_googleapi = array();
                    $current_website_googleapi = $this->getGoogleApi();
                } else {
                    $current_website_googleapi = '';
                }
                $viewModel = new ViewModel(array(
                    'client_websites' => $client_websites,
                    'current_website_id' => $current_website_id,
                    'website_data' => $current_website_googleapi,
                ));
            } else {
                $session->offsetSet('daterange', '');
                $viewModel = new ViewModel(array(
                    'client_websites' => $client_websites,
                    'current_website_id' => $current_website_id,
                ));
            }
            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function getGoogleApi() {
        if ($user = $this->identity()) {
//             error_reporting(E_ALL);
//            ini_set('display_errors', '1');
            $session = new Container('googleapi');
            $from = $session->offsetGet('from');
            $till = $session->offsetGet('till');
            $website_id = $session->offsetGet('current_website_id');
            $id = $session->offsetGet('id');
            $tableGatewayWebsite = $this->getConnectionWebsite();
            $websiteTable = new WebsiteTable($tableGatewayWebsite);
            $profile_id = $websiteTable->getWebsite($id);
            //;  print_r($profile_id);exit;
            $ga = new gapi('seolawyers2012@gmail.com ', '9382devilx');
            /* We are using the 'source' dimension and the 'visits' metrics */
            $dimensions = array('pagePath');
            $metrics = array('pageviews');
            $ga->requestReportData($profile_id->profile_id, $dimensions, $metrics, '-pageviews', '', $from, $till, 1, 10);
            $gaResults = $ga->getResults();
            $i = 0;
            $google_api_data = array();
            foreach ($gaResults as $result) {
                $google_api_data[$i]['path'] = $result;
                $google_api_data[$i]['pageviews'] = $result->getPageviews();

                $i = $i + 1;
            }
//             to get Organic Search
            $dimensions = array('medium');
            $metrics = array('sessions');
            $filter = 'medium == organic';
            $ga->requestReportData($profile_id->profile_id, $dimensions, $metrics, '', $filter, $from, $till, 1, 10);
            $gaResults = $ga->getResults();
            $i = 0;
            foreach ($gaResults as $result) {
                $google_api_data['organic'] = $result->getSessions();
            }

            //to get total session

            $dimensions = array('channelGrouping');
            $metrics = array('sessions');
            $ga->requestReportData($profile_id->profile_id, $dimensions, $metrics, '', '', $from, $till, 1, 10);
            $gaResults = $ga->getResults();
            $total = 0;
            foreach ($gaResults as $result) {
                 $total= $total + $result->getSessions() ;
            }
            $google_api_data['total_session']=$total;
            return $google_api_data;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function daterangeAction() {      // finding daterange data from database
        if ($user = $this->identity()) {
            $daterange = $_GET['daterange'];
            $website_id = $_GET['websiteid'];
            $id = $_GET['id'];

            $ranges = explode('-', $daterange);
            $all_ranges = array();
            foreach ($ranges as $range) {
                $range = trim($range);
                $parts = explode(' ', $range);
                $month = date("m", strtotime($parts[0]));
                $day = rtrim($parts[1], ',');
                $all_ranges[] = $parts[2] . '-' . $month . '-' . sprintf("%02s", $day);
            }
            $session = new Container('googleapi');
            $session->offsetSet('current_website_id', $website_id);
            $session->offsetSet('id', $id);
            $session->offsetSet('from', $all_ranges[0]);
            $session->offsetSet('till', $all_ranges[1]);
            $session->offsetSet('daterange', $daterange);
            $link_client_id = $session->offsetGet('googleapi_client_id');
            return $this->redirect()->toUrl('/googleapi/index/' . $link_client_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function changewebsiteAction() {
        if ($user = $this->identity()) {
            $website_id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('googleapi');
            $googleapi_client_id = $session->offsetGet('googleapi_client_id');
            $session->offsetSet('current_website_id', $website_id);
            $session->offsetSet('msg', "");
            return $this->redirect()->toUrl('/googleapi/index/' . $googleapi_client_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function getConnectionWebsite() {        // set connection to Website table
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\Website);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('websites', $dbAdapter, null, $resultSetPrototype);
        return $tableGateway;
    }

}
