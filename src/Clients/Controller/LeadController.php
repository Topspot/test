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
use Zend\Debug\Debug;
use Zend\Session\Container;
use Clients\Model\Lead;
use Clients\Model\LeadTable;

class LeadController extends AbstractActionController {

    public function indexAction() {
//         $website_id = (int) $this->params()->fromRoute('id', 0);
      $post = $this->request->isPost();
         print("herobrrrrrrt");
         print($post);exit;
//     print_r($_GET);exit;
//        return $post;
//        $tableGateway = $this->getConnection();
//        $leadTable = new LeadTable($tableGateway);
//        $lead = new Lead();
//        $lead->name = $_REQUEST;
//        $id = $leadTable->saveLead($lead);
//        print_r($_REQUEST);exit;
//        $lead->exchangeArray($post);
        
        
    }

    public function getConnection() {
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\Lead);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('leads', $dbAdapter, null, $resultSetPrototype);
        return $tableGateway;
    }

}
