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
use Zend\View\Model\ViewModel;
use Clients\Form\AddForm;
use Clients\Form\AddFilter;
use Clients\Form\EditForm;
use Clients\Form\EditFilter;
use Clients\Model\Client;
use Clients\Model\ClientTable;
use Clients\Model\Website;
use Clients\Model\WebsiteTable;
use Zend\Session\Container;
use Clients\Model\UserRightTable;
use Clients\Model\UserRight;
use Clients\Model\Transcript;
use Clients\Model\TranscriptTable;
use Clients\Model\Lead;
use Clients\Model\LeadTable;
use Clients\Model\Link;
use Clients\Model\LinkTable;
use Clients\Model\Book;
use Clients\Model\BookTable;
use PHPExcel;
use Excel2007;
use IOFactory;

class IndexController extends AbstractActionController {

    public function indexAction() {
        if ($user = $this->identity()) {
            return new ViewModel();
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function selectAction() {
        if ($user = $this->identity()) {

            $id = (int) $this->params()->fromRoute('id', 0);
//         print_r();exit;
            if ($id == 0) {
                print_r("Could not find ID");
                exit;
            }
            $session = new Container('link');
            $session->offsetSet('selected_client_id', $id);
            
            $tableGateway = $this->getConnection();
            $clientTable = new ClientTable($tableGateway);
            $clients=$clientTable->getClient($id);
            
            $tableGatewayWebsite = $this->getConnectionWebsite();
            $websiteTable = new WebsiteTable($tableGatewayWebsite);
            $client_websites=$websiteTable->getWebsiteClients($id);
            
            $viewModel = new ViewModel(array(
                'id' => $id,
                'clients' => $clients,
                'client_websites' => $client_websites,
            ));
            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function reportAction() {
        if ($user = $this->identity()) {
            $daterange = $_GET['daterange'];
            $website_id = $_GET['websiteid'];
//            print_r($daterange. '---------'.$website_id);exit;
            $ranges = explode('-', $daterange);
            $all_ranges = array();
            foreach ($ranges as $range) {
                $range = trim($range);
                $parts = explode(' ', $range);
                $month = date("m", strtotime($parts[0]));
                $day = rtrim($parts[1], ',');
                $all_ranges[] = $parts[2] . '-' . $month . '-' . sprintf("%02s", $day);
            }
            $from = $all_ranges[0];
            $till = $all_ranges[1];

            //link
            $tableGatewayLink = $this->getConnectionLink();
            $linkTable = new LinkTable($tableGatewayLink);
            $website_links_data = $linkTable->alldateRange($from, $till);

            //lead
            $tableGatewayLead = $this->getConnectionLead();
            $leadTable = new LeadTable($tableGatewayLead);
            $website_leads_data = $leadTable->alldateRange($from, $till);

            //book            
            $tableGatewayBook = $this->getConnectionBook();
            $bookTable = new BookTable($tableGatewayBook);
            $website_books_data = $bookTable->alldateRange($from, $till);

            //transcript
            $from = $from . ' 00:00:00';
            $till = $till . ' 23:59:59';
            $tableGatewayTranscript = $this->getConnectionTranscript();
            $transcriptTable = new TranscriptTable($tableGatewayTranscript);
            $website_transcripts_data = $transcriptTable->alldateRange($from, $till);
//            print_r($website_transcripts_data);
//           exit;
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
            $cell = 2;
              $rowCount = count($website_data);
            if ($rowCount > 0) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'LINKS');
            $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setBold(true);
            
          
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $cell, 'Date')
                        ->setCellValue('B' . $cell, 'URL');

                $objPHPExcel->getActiveSheet()->getStyle('A' . $cell . ':B' . $cell)->getFont()->setBold(true);

//                    print_r($website_links_data);
                foreach ($website_links_data as $link) {
                    $cell = $cell + 1;
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $cell, $link->date)
                            ->setCellValue('B' . $cell, $link->url);
                }
                $cell = $cell + 1;
            }
            $rowCount = count($website_transcripts_data);
            if ($rowCount > 0) {
                // Add some data
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $cell, 'TRANSCRIPTS');
                $objPHPExcel->getActiveSheet()->getStyle('A' . $cell . ':B' . $cell)->getFont()->setBold(true);
                $cell = $cell + 1;
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $cell, 'Name')
                        ->setCellValue('B' . $cell, 'Date Recevied')
                        ->setCellValue('C' . $cell, 'Date Posted')
                        ->setCellValue('D' . $cell, 'Date Revised');
                $objPHPExcel->getActiveSheet()->getStyle('A' . $cell . ':D' . $cell)->getFont()->setBold(true);
                foreach ($website_transcripts_data as $transcripts) {
                    $cell = $cell + 1;
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $cell, $transcripts->name)
                            ->setCellValue('C' . $cell, $transcripts->date_received)
                            ->setCellValue('B' . $cell, $transcripts->date_posted)
                            ->setCellValue('D' . $cell, $transcripts->date_revised);
                }
                $cell = $cell + 1;
            }
            $rowCount = count($website_leads_data);
            if ($rowCount > 0) {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $cell, 'LEADS');
                $objPHPExcel->getActiveSheet()->getStyle('A' . $cell . ':B' . $cell)->getFont()->setBold(true);
                $cell = $cell + 1;
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $cell, 'Caller Type')
                        ->setCellValue('B' . $cell, 'Lead Date')
                        ->setCellValue('C' . $cell, 'Lead Source')
                        ->setCellValue('D' . $cell, 'Incomming Ph')
                        ->setCellValue('E' . $cell, 'Call Duration')
                        ->setCellValue('F' . $cell, 'Leads Name')
                        ->setCellValue('G' . $cell, 'Leads email');
                $objPHPExcel->getActiveSheet()->getStyle('A' . $cell . ':G' . $cell)->getFont()->setBold(true);

                foreach ($website_leads_data as $leads) {
                    $cell = $cell + 1;
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $cell, $leads->caller_type)
                            ->setCellValue('B' . $cell, $leads->lead_date)
                            ->setCellValue('C' . $cell, $leads->lead_source)
                            ->setCellValue('D' . $cell, $leads->inc_phone)
                            ->setCellValue('E' . $cell, $leads->call_duration)
                            ->setCellValue('F' . $cell, $leads->lead_name)
                            ->setCellValue('G' . $cell, $leads->lead_email);
                }

                $cell = $cell + 1;
            }
            $rowCount = count($website_books_data);
            if ($rowCount > 0) {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $cell, 'BOOKS');
                $objPHPExcel->getActiveSheet()->getStyle('A' . $cell . ':B' . $cell)->getFont()->setBold(true);
                $cell = $cell + 1;
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $cell, 'Name');
                $objPHPExcel->getActiveSheet()->getStyle('A' . $cell . ':G' . $cell)->getFont()->setBold(true);
                foreach ($website_books_data as $book) {
                    $cell = $cell + 1;
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $cell, $book->name);
                }
            }

// Rename worksheet
            $objPHPExcel->getActiveSheet()->setTitle('Reports');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a clientâ€™s web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="report.xlsx"');
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

    public function listAction() {
        if ($user = $this->identity()) {

            //get current user data
            $auth = new AuthenticationService();
            $user_data = $auth->getIdentity();

            $session = new Container('link');
            $delete_msg = $session->offsetGet('delete_user_msg');
            $session->offsetSet('selected_client_id', '');
            $tableGateway = $this->getConnection();
            $clientTable = new ClientTable($tableGateway);
            
            $tableGatewayWebsite = $this->getConnectionWebsite();
            $websiteTable = new WebsiteTable($tableGatewayWebsite);
            $tableGatewayUserRights = $this->getConnectionUserRights();
            $UserRight = new UserRightTable($tableGatewayUserRights);
            if ($auth->getIdentity()->roles_id == 2) {
                $applying_user_rights = $UserRight->getUserRightUser($user_data->usr_id);
            } else {
                $applying_user_rights = '';
            }
            if (isset($delete_msg) && $delete_msg != '') {
                $viewModel = new ViewModel(array(
                    'clients' => $clientTable->fetchAll(),
                    'websites' => $websiteTable->fetchAll(),
                    'message' => $delete_msg,
                    'applying_user_rights' => $applying_user_rights
                ));
            } else {
                $viewModel = new ViewModel(array(
                    'clients' => $clientTable->fetchAll(),
                    'websites' => $websiteTable->fetchAll(),
                    'applying_user_rights' => $applying_user_rights
                ));
            }

            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function addAction() {
        if ($user = $this->identity()) {
            $form = new AddForm();
            if ($this->request->isPost()) {
                $tableGateway = $this->getConnection();
                $post = $this->request->getPost();
                $client = new Client();


                $client->exchangeArray($post);
                $clientTable = new ClientTable($tableGateway);
                $id = $clientTable->saveClient($client);
//            $id->buffer();
                $tableGatewayWebsite = $this->getConnectionWebsite();
                $websiteTable = new WebsiteTable($tableGatewayWebsite);
                $this->insertWebsiteAction($post->website, $id, $websiteTable);

                $session = new Container('link');
                $session->offsetSet('delete_user_msg', "Client has been Created");
                return $this->redirect()->toRoute(NULL, array(
                            'controller' => 'index',
                            'action' => 'list'
                ));
            }


            $viewModel = new ViewModel(array('form' => $form));
            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    // insert multiple website
    public function insertWebsiteAction($client_website, $id, $websiteTable) {
        if ($user = $this->identity()) {
            $website = new Website();
            $multiple_websites = explode(",", $client_website);

            foreach ($multiple_websites as $web) {
                $data = array(
                    'clients_id' => $id,
                    'website' => $web,
                );

                $website->exchangeArray($data);
                $websiteTable->saveWebsite($website);
            }
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function editAction() {
        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);
            if (!$id) {
                return $this->redirect()->toRoute(NULL, array(
                            'controller' => 'index',
                            'action' => 'add'
                ));
            }
            $tableGateway = $this->getConnection();
            $clientTable = new ClientTable($tableGateway);

            $tableGatewayWebsite = $this->getConnectionWebsite();
            $websiteTable = new WebsiteTable($tableGatewayWebsite);

            $form = new EditForm();
            if ($this->request->isPost()) {

                $post = $this->request->getPost();
                //saving Client data table
                $client = $clientTable->getClient($post->id);

                $form->bind($client);
                $form->setData($post);
                $client->name = $post->name;
                $client->phone = $post->phone;
                $client->email = $post->email;
                $client->calltracking = $post->calltracking;
                $clientTable->saveClient($client);

                //delete all website with this id
                $websiteTable->deleteWebsiteClient($post->id);
                //inserting Edited value in database
                $this->insertWebsiteAction($post->website, $post->id, $websiteTable);


                $session = new Container('link');
                $session->offsetSet('delete_user_msg', "Client has been Updated");
                return $this->redirect()->toRoute(NULL, array(
                            'controller' => 'index',
                            'action' => 'list'
                ));
            }
            $client = $websiteTable->getWebsiteClients($this->params()->fromRoute('id'));  //get websites fromclients_id
            foreach ($client as $c) {

                $all_websites .="$c->website ";
            }
            $all_webs = str_replace(" ", ",", $all_websites); //making string of websites
            $all_web = preg_replace('/,[^,]*$/', '', $all_webs); //remove the last comma

            $client = $clientTable->getClient($this->params()->fromRoute('id'));

            $form->bind($client);


            $viewModel = new ViewModel(array(
                'form' => $form,
                'client_id' => $this->params()->fromRoute('id'),
                'website' => $all_web,
            ));
            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function deleteAction() {
        header('Content-Type: application/json');

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute(NULL, array(
                        'controller' => 'index',
                        'action' => 'list'
            ));
        }
        //delete client
        $tableGateway = $this->getConnection();
        $clientTable = new ClientTable($tableGateway);
        $clientTable->deleteClient($id);

        //delete all websites for this clients
        $tableGatewayWebsite = $this->getConnectionWebsite();
        $websiteTable = new WebsiteTable($tableGatewayWebsite);
        $websiteTable->deleteWebsiteClient($id);

        echo json_encode(array('text' => 'omrele'));
        exit();
    }

    public function setmessageAction() {
        if ($user = $this->identity()) {
            $session = new Container('link');
            $session->offsetSet('delete_user_msg', "Client has been Deleted");

            return $this->redirect()->toRoute(NULL, array(
                        'controller' => 'index',
                        'action' => 'list'
            ));
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function getConnection() {
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\Client);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('clients', $dbAdapter, null, $resultSetPrototype);
        return $tableGateway;
    }

    public function getConnectionWebsite() {
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

    public function getConnectionLink() {        // set connection to Link table
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\Link);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('links', $dbAdapter, null, $resultSetPrototype);
        return $tableGateway;
    }

    public function getConnectionTranscript() {        // set connection to transcript table
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\Transcript);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('transcripts', $dbAdapter, null, $resultSetPrototype);
        return $tableGateway;
    }

    public function getConnectionLead() {        // set connection to transcript table
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\Lead);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('leads', $dbAdapter, null, $resultSetPrototype);
        return $tableGateway;
    }

    public function getConnectionBook() {        // set connection to Book table
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\Book);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('books', $dbAdapter, null, $resultSetPrototype);
        return $tableGateway;
    }

}
