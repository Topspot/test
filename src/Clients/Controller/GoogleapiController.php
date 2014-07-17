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
use gapi;
//use Google_Client;
//use Google_AnalyticsService;

//use GData;
//use App;
//use Analytics;
//use ClientLogin;

/* Set your Google Analytics credentials */

//define('ga_account'     ,'seolawyers2012@gmail.com');
//define('ga_password'    ,'9382devilx');
//define('ga_profile_id'  ,'');

class GoogleapiController extends AbstractActionController {
    /* Set your Google Analytics credentials */

    public function indexAction() {
//        $ga = new gapi('lisapelosi1@gmail.com', 'Topspot@123');
        $ga = new gapi('seolawyers2012@gmail.com ', '9382devilx');
        /* We are using the 'source' dimension and the 'visits' metrics */
//        $dimensions = array('landingPagePath');
        $dimensions = array('channelGrouping');

//        $metrics = array('pageviews');
        $metrics = array('sessions');
        $filter = 'channelGrouping == Organic Search';
//        $fromDate = date('Y-m-d', strtotime('-2 days'));
//        $toDate = date('Y-m-d');
        /* We will sort the result be desending order of visits, 
          and hence the '-' sign before the 'visits' string */
//        $ga->requestReportData('76725909', $dimensions, $metrics, '-visits');
        $ga->requestReportData('66890150', $dimensions, $metrics, '-sessions',$filter,'2014-06-16','2014-07-16',1,10);

        $gaResults = $ga->getResults();

        $i = 1;
        ?>
        <table>
            <tr>

                
                <th>Paths</th>
                <th>Pageviews</th>
<!--                <th>Visits</th>
                <th>source</th>
                <th>region</th>-->
            </tr>


            <?php
            foreach ($gaResults as $result) {
                ?>
                <tr>
                  <td><?php echo $result ?></td>
                    <td><?php echo $result->getSessions() ?></td>
                    <!--<td><?php// echo $result->getLandingpagepath() ?></td>-->
                     <!--<td><?php //echo $result->getVisits() ?></td>-->
<!--                    <td><?php //echo $result->getVisitors() ?></td>
                   
                    <td><?php //echo $result->getSource() ?></td>
                    <td><?php //echo $result->getRegion() ?></td>-->
                </tr>
            <?php
//                     echo '<strong>'.$result.'</strong><br />';
//  echo 'Source: ' . $result->getSource() . ' ';
//  echo 'Visits: ' . $result->getVisits() . '<br />';
//  echo 'Region: ' . $result->getRegion() . '<br />';
//  echo 'Page Views: ' . $result->UniquePageviews() . '<br />';
//            printf("%-4d %-40s %5d\n", $i++, $result->getSource(), $result->getVisits());
        }
        ?>
        </table>
        <?php
//        echo "Total Results : {$ga->getTotalResults()}";
//        echo '<p>Total Source: ' . $ga->getSource() . ' total visits: ' . $ga->getVisits() . '</p>';
        exit;
    }

}
