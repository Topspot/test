<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @lead      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Clients\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationService;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;
use Zend\Session\Container;
use Clients\Model\Lead;
use Clients\Model\LeadTable;
use Clients\Model\Website;
use Clients\Model\WebsiteTable;
use Clients\Form\AddLeadForm;
use Clients\Form\AddLeadFilter;
use Clients\Form\EditLeadForm;
use Clients\Form\EditLeadFilter;
use PHPExcel;
use Excel2007;
use IOFactory;
use Clients\Model\UserRightTable;
use Clients\Model\UserRight;

//use Zend\Session\Container; // We need this when using sessions
//use Zend\Session\Storage\ArrayStorage;
//use Zend\Session\SessionManager;

class LeadController extends AbstractActionController {


    public function leaddataAction() {
        if ($_POST) {
            $data = $_POST;
            $tableGatewayWebsite = $this->getConnectionWebsite();
            $websiteTable = new WebsiteTable($tableGatewayWebsite);
            $website_data = $websiteTable->getWebsiteByName($data['website']);
             $lead = new Lead();
            if ($website_data) {
                $website_id = $website_data->id;
                $lead->website_id = $website_id;
            } else {
               $lead->website_id = '';
            }
            $tableGateway = $this->getConnection();
            $leadTable = new LeadTable($tableGateway);

           
            $lead->comments = $data['comments'];
            $lead->caller_type = $data['caller_type'];
            $date = explode('/', $data['lead_date']);
            $lead->lead_date = $date[2] . '-' . $date[0] . '-' . $date[1];
            $lead->lead_source = $data['lead_source'];
            $lead->client_name = $data['client_name'];
            $lead->website = $data['website'];
            $lead->inc_phone = $data['inc_phone'];
            $lead->call_time = $data['call_time'];
            $lead->call_duration = $data['call_duration'];
            $lead->lead_name = $data['lead_name'];
            $lead->lead_email = $data['lead_email'];
            $id = $leadTable->saveLead($lead);
            return 0;
        }
    }

    public function indexAction() {

        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);
             //get current user data
            $auth = new AuthenticationService();
            $user_data=$auth->getIdentity();
            
            $session = new Container('lead');
            $session->offsetSet('lead_client_id', $id);
            if (!$id) {
                print_r("cant find ID");
                exit;
            }
            $tableGatewayWebsite = $this->getConnectionWebsite();
            $websiteTable = new WebsiteTable($tableGatewayWebsite);

            $tableGateway = $this->getConnection();
            $leadTable = new LeadTable($tableGateway);
            
            $tableGatewayUserRights = $this->getConnectionUserRights();
            $UserRight = new UserRightTable($tableGatewayUserRights);
             if ($auth->getIdentity()->roles_id == 2) {
                  $applying_user_rights=$UserRight->getUserRightUser($user_data->usr_id);
             }else{
                  $applying_user_rights='';
             }

            if ($session->offsetExists('current_website_id') && $session->offsetGet('current_website_id') != '') {
                $current_website_id = $session->offsetGet('current_website_id');
                if ($session->offsetExists('from') && $session->offsetGet('from') != '') {

                    $current_website_lead = $this->setDateRange();
                } else {
                    $current_website_lead = $leadTable->getLeadWebsite($current_website_id);
                }
                if (!empty($current_website_lead)) {

                    $viewModel = new ViewModel(array(
                        'client_websites' => $websiteTable->getWebsiteClients($id),
                        'message' => $session->offsetGet('msg'),
                        'website_data' => $current_website_lead,
                        'current_website_id' => $current_website_id,
                        'applying_user_rights' => $applying_user_rights
                    ));
                } else {

                    $viewModel = new ViewModel(array(
                        'client_websites' => $websiteTable->getWebsiteClients($id),
                        'message' => $session->offsetGet('msg'),
                        'website_data' => $current_website_lead,
                        'current_website_id' => $current_website_id,
                        'applying_user_rights' => $applying_user_rights
                    ));
                }
            } else {
                $client_websites = $websiteTable->getWebsiteClients($id);

                foreach ($client_websites as $value) {
                    $current_website_id = $value->id;
                    $current_website_lead = $leadTable->getLeadWebsite($value->id);
                    break;
                }

                $viewModel = new ViewModel(array(
                    'client_websites' => $client_websites,
                    'website_data' => $current_website_lead,
                    'current_website_id' => $current_website_id,
                    'applying_user_rights' => $applying_user_rights
                ));
            }
            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function exportdataAction() {
         if ($user = $this->identity()) {
        $num = (int) $this->params()->fromRoute('id', 0);
        $session = new Container('lead');
//                    ini_set("display_errors", "1");
//            error_reporting(E_ALL & ~E_NOTICE);
// Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

// Set document properties
        $objPHPExcel->getProperties()->setCreator("Speak Easy Marketing Inc")
                ->setLastModifiedBy("Maarten Balliauw")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
// Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Caller Type')
                ->setCellValue('B1', 'Lead Date')
                ->setCellValue('C1', 'Lead Source')
                ->setCellValue('D1', 'Incomming Ph')
                ->setCellValue('E1', 'Call Duration')
                ->setCellValue('F1', 'Leads Name')
                ->setCellValue('G1', 'Leads email');

        $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);
        for ($i = 0; $i <= $num; $i++) {
            $data = $session->offsetGet('leadobject' . $i);
            $cell = $i + 2;
            if ($data->caller_type == 1) {
                $name = "Poten Newclient";
            } else if ($data->caller_type == 2) {
                $name = "Non-client";
            } else if ($data->caller_type == 3) {
                $name = "Soliciter";
            } else if ($data->caller_type == 4) {
                $name = "Current Client";
            } else if ($data->caller_type == 5) {
                $name = "Repeated";
            } else if ($data->caller_type == 6) {
                $name = "Web Formt";
            } else if ($data->caller_type == 7) {
                $name = "Test call";
            } else {
                $name = "No Recording";
            }

            if ($data->lead_source == 1) {
                $lead_src = "Website-NY";
            } else if ($data->lead_source == 2) {
                $lead_src = "Contact Form";
            } else {
                $lead_src = "Book Download";
            }
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $cell, $name)
                    ->setCellValue('B' . $cell, $data->lead_date)
                    ->setCellValue('C' . $cell, $lead_src)
                    ->setCellValue('D' . $cell, $data->inc_phone)
                    ->setCellValue('E' . $cell, $data->call_duration)
                    ->setCellValue('F' . $cell, $data->lead_name)
                    ->setCellValue('G' . $cell, $data->lead_email);
        }
// Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Leads');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
// Redirect output to a clientâ€™s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Leads.xlsx"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function addAction() {
        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);

            $session = new Container('lead');
            $lead_client_id = $session->offsetGet('lead_client_id');
            $session->offsetSet('current_website_id', $id);

            if (!$id) {
                return $this->redirect()->toRoute(NULL, array(
//                        'controller' => 'lead',
                            'action' => 'index',
                            'id' => $lead_client_id
                ));
            }
            $form = new AddLeadForm();
            if ($this->request->isPost()) {
                $tableGateway = $this->getConnection();
                $post = $this->request->getPost();

                $post->website_id = $id;

                $originalDate = $post->lead_date;
                $newDate = date("Y-m-d", strtotime($originalDate));
                $post->lead_date = $newDate;
//              print_r($post);exit;
                $lead = new Lead();
                $lead->exchangeArray($post);
                $leadTable = new LeadTable($tableGateway);

                $id = $leadTable->saveLead($lead);
                $session->offsetSet('msg', "Lead has been successfully Added.");
                return $this->redirect()->toUrl('/lead/index/' . $lead_client_id);
            }

            //print_r('here');exit;
            $viewModel = new ViewModel(array('form' => $form, 'id' => $id));
            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function changewebsiteAction() {
        if ($user = $this->identity()) {
            $website_id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('lead');
            $lead_client_id = $session->offsetGet('lead_client_id');
            $session->offsetSet('current_website_id', $website_id);
            $session->offsetSet('msg', "");
            return $this->redirect()->toUrl('/lead/index/' . $lead_client_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
//         print_r($website_id);exit;
    }

    public function editAction() {

        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('lead');
            $lead_client_id = $session->offsetGet('lead_client_id');
//        $session->offsetSet('current_website_id', $id);
            $session->offsetSet('msg', "Lead has been successfully Updated.");
            if (!$id) {
                return $this->redirect()->toRoute(NULL, array(
                            'controller' => 'index',
                            'action' => 'add'
                ));
            }
            $tableGateway = $this->getConnection();
            $leadTable = new LeadTable($tableGateway);


            $form = new EditLeadForm();
            if ($this->request->isPost()) {
                $auth = new AuthenticationService();
                $post = $this->request->getPost();
                //saving Client data table
                $lead = $leadTable->getLead($post->id);

                $form->bind($lead);
                $form->setData($post);
//            print_r($post);exit;
                $originalDate = $post->lead_date;
                $newDate = date("Y-m-d", strtotime($originalDate));
                if ($auth->getIdentity()->roles_id == 2) {
                    
                } else {
                    $post->lead_date = $newDate;
                    $lead->lead_source = $post->lead_source;
                    $lead->inc_phone = $post->inc_phone;
                    $lead->call_duration = $post->call_duration;
                }
                $lead->caller_type = $post->caller_type;

                $lead->lead_name = $post->lead_name;
                $lead->lead_email = $post->lead_email;
                $session->offsetSet('current_website_id', $lead->website_id);
//             print_r($lead);exit;
                $leadTable->saveLead($lead);
                return $this->redirect()->toUrl('/lead/index/' . $lead_client_id);
            }
            $lead = $leadTable->getLead($this->params()->fromRoute('id'));
            $originalDate = $lead->date;
            $newDate = date("m/d/Y", strtotime($originalDate));
            $lead->date = $newDate;
            $form->bind($lead); //biding data to form
            $viewModel = new ViewModel(array(
                'form' => $form,
                'id' => $this->params()->fromRoute('id'),
            ));
            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function deleteAction() {
        header('Content-Type: application/json');

        $id = (int) $this->params()->fromRoute('id', 0);
//                    Debug::dump($id);exit;
        if (!$id) {
            return $this->redirect()->toRoute(NULL, array(
                        'controller' => 'index',
                        'action' => 'list'
            ));
        }
        //delete Lead for a client website
        $tableGateway = $this->getConnection();
        $leadTable = new LeadTable($tableGateway);
        $leadTable->deleteLead($id);


        echo json_encode(array('data' => ''));
        exit();
    }

    public function getLeadByIdAction() {
        header('Content-Type: application/json');
        $id = (int) $this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute(NULL, array(
                        'controller' => 'index',
                        'action' => 'list'
            ));
        }
        $tableGateway = $this->getConnection();
        $leadTable = new LeadTable($tableGateway);
        $data = $leadTable->getLeadWebsite($id);
        echo json_encode(array('data' => (array) $data));
        exit();
    }

    public function setDateRange() {
        if ($user = $this->identity()) {
            $session = new Container('lead');
            $from = $session->offsetGet('from');
            $till = $session->offsetGet('till');
            $website_id = $session->offsetGet('current_website_id');
            $tableGateway = $this->getConnection();
            $leadTable = new LeadTable($tableGateway);
            $website_leads_data = $leadTable->dateRange($from, $till, $website_id);
//        print_r($website_leads_data);exit;
            return $website_leads_data;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function daterangeAction() {      // finding daterange data from database
        if ($user = $this->identity()) {
            $daterange = $_GET['daterange'];
            $website_id = $_GET['websiteid'];

            $ranges = explode('-', $daterange);
            $all_ranges = array();
            foreach ($ranges as $range) {
                $range = trim($range);
                $parts = explode(' ', $range);
                $month = date("m", strtotime($parts[0]));
                $day = rtrim($parts[1], ',');
                $all_ranges[] = $parts[2] . '-' . $month . '-' . $day;
            }

            $session = new Container('lead');
            $session->offsetSet('current_website_id', $website_id);
            $session->offsetSet('from', $all_ranges[0]);
            $session->offsetSet('till', $all_ranges[1]);
            $session->offsetSet('daterange', $daterange);
            $lead_client_id = $session->offsetGet('lead_client_id');
            return $this->redirect()->toUrl('/lead/index/' . $lead_client_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function setmessageAction() {  // set message for delete client lead
        if ($user = $this->identity()) {
            $session = new Container('lead');
            $lead_client_id = $session->offsetGet('lead_client_id');
            $website_id = (int) $this->params()->fromRoute('id', 0);
            $session->offsetSet('current_website_id', $website_id);
            $session->offsetSet('msg', "Lead has been successfully Deleted.");
//        print_r($website_id);exit;
            return $this->redirect()->toUrl('/lead/index/' . $lead_client_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
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

    public function getConnectionWebsite() {        // set connection to Website table
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\Website);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('websites', $dbAdapter, null, $resultSetPrototype);
        return $tableGateway;
    }
    
        public function getConnectionUserRights() {        // set connection to User Rights table
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\UserRight);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('user_rights', $dbAdapter, null, $resultSetPrototype);
        return $tableGateway;
    }

}
