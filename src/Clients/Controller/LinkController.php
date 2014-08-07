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
use Zend\Authentication\AuthenticationService;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;
use Clients\Model\Website;
use Clients\Model\WebsiteTable;
use Clients\Model\UserRightTable;
use Clients\Model\UserRight;
use Clients\Model\Link;
use Clients\Model\LinkTable;
use Clients\Form\AddLinkForm;
use Clients\Form\AddLinkFilter;
use Clients\Form\EditLinkForm;
use Clients\Form\EditLinkFilter;
use Zend\Session\Container;
use PHPExcel;
use Excel2007;
use IOFactory;

class LinkController extends AbstractActionController {

    public function indexAction() {
        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('link');
            $session->offsetSet('link_client_id', $id);
            
            //get current user data
            $auth = new AuthenticationService();
            $user_data=$auth->getIdentity();


            if (!$id) {
                return $this->redirect()->toRoute(NULL, array(
                            'controller' => 'index',
                            'action' => 'list'
                ));
            }
            $tableGatewayWebsite = $this->getConnectionWebsite();
            $websiteTable = new WebsiteTable($tableGatewayWebsite);

            $tableGateway = $this->getConnection();
            $linkTable = new LinkTable($tableGateway);
            
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
                    $current_website_link = $this->setDateRange();
                } else {
                    $current_website_link = $linkTable->getLinkWebsite($current_website_id);
                }
//                print_r();
                if (!empty($current_website_link)) {

                    $viewModel = new ViewModel(array(
                        'client_websites' => $websiteTable->getWebsiteClients($id),
                        'message' => $session->offsetGet('msg'),
                        'website_data' => $current_website_link,
                        'current_website_id' => $current_website_id,
                        'applying_user_rights' => $applying_user_rights
                            
                    ));
                } else {
                    
                    $viewModel = new ViewModel(array(
                        'client_websites' => $websiteTable->getWebsiteClients($id),
                        'message' => $session->offsetGet('msg'),
                        'website_data' => $current_website_link,
                        'current_website_id' => $current_website_id,
                         'applying_user_rights' => $applying_user_rights
                    ));
                }
            } else {

                $client_websites = $websiteTable->getWebsiteClients($id);
                foreach ($client_websites as $value) {
                    $current_website_id = $value->id;
                    $current_website_link = $linkTable->getLinkWebsite($value->id);
                    break;
                }
                $session->offsetSet('daterange', '');
                $viewModel = new ViewModel(array(
                    'client_websites' => $client_websites,
                    'website_data' => $current_website_link,
                    'current_website_id' => $current_website_id,
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
            $id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('link');
            $link_client_id = $session->offsetGet('link_client_id');
            $session->offsetSet('current_website_id', $id);

            if (!$id) {
                return $this->redirect()->toRoute(NULL, array(
//                        'controller' => 'link',
                            'action' => 'index',
                            'id' => $link_client_id
                ));
            }
            $form = new AddLinkForm();
            if ($this->request->isPost()) {
//                print_r("POST");exit;
                $tableGateway = $this->getConnection();
                $post = $this->request->getPost();
                $post->website_id = $id;
                $originalDate = $post->date;
                $newDate = date("Y-m-d", strtotime($originalDate));
                $post->date = $newDate;
                $link = new Link();
                $link->exchangeArray($post);
                $linkTable = new LinkTable($tableGateway);

                $id = $linkTable->saveLink($link);
                $session->offsetSet('msg', "Link has been successfully Added.");
                return $this->redirect()->toUrl('/link/index/' . $link_client_id);
            }


            $viewModel = new ViewModel(array('form' => $form, 'id' => $id,'link_client_id' => $link_client_id));
//            $viewModel->setTerminal(true);
            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }
     public function exportdataAction() {
         if ($user = $this->identity()) {
        $num = (int) $this->params()->fromRoute('id', 0);
        $session = new Container('link');
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
                ->setCellValue('A1', 'Date')
                ->setCellValue('B1', 'URL');

        $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setBold(true);
        for ($i = 0; $i <= $num; $i++) {
            $data = $session->offsetGet('leadobject' . $i);
            $cell = $i + 2;
           
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $cell, $data->date)
                    ->setCellValue('B' . $cell, $data->url);
        }
// Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Links');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        
// Redirect output to a clientâ€™s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Links.xlsx"');
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
    public function changewebsiteAction() {
        if ($user = $this->identity()) {
            $website_id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('link');
            $link_client_id = $session->offsetGet('link_client_id');
            $session->offsetSet('current_website_id', $website_id);
            $session->offsetSet('msg', "");
            return $this->redirect()->toUrl('/link/index/' . $link_client_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
//         print_r($website_id);exit;
    }

    public function editAction() {
        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('link');
            $link_client_id = $session->offsetGet('link_client_id');
//        $session->offsetSet('current_website_id', $id);
            $session->offsetSet('msg', "Link has been successfully Updated.");
            if (!$id) {
                return $this->redirect()->toRoute(NULL, array(
                            'controller' => 'index',
                            'action' => 'add'
                ));
            }
            $tableGateway = $this->getConnection();
            $linkTable = new LinkTable($tableGateway);


            $form = new EditLinkForm();
            if ($this->request->isPost()) {

                $post = $this->request->getPost();
                //saving Client data table
                $link = $linkTable->getLink($post->id);

                $form->bind($link);
                $form->setData($post);
                $originalDate = $post->date;
                $newDate = date("Y-m-d", strtotime($originalDate));
                $post->date = $newDate;
                $link->date = $post->date;
                $link->url = $post->url;
                $session->offsetSet('current_website_id', $link->website_id);

                $linkTable->saveLink($link);


                return $this->redirect()->toUrl('/link/index/' . $link_client_id);
            }
            $link = $linkTable->getLink($this->params()->fromRoute('id'));
            $originalDate = $link->date;
            $newDate = date("m/d/Y", strtotime($originalDate));
            $link->date = $newDate;
            $form->bind($link); //biding data to form
            $viewModel = new ViewModel(array(
                'form' => $form,
                'id' => $this->params()->fromRoute('id'),
                'link_client_id' => $link_client_id,
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
        //delete Link for a client website
        $tableGateway = $this->getConnection();
        $linkTable = new LinkTable($tableGateway);
//        $data=$linkTable->getLink($id);
        $linkTable->deleteLink($id);


        echo json_encode(array('data' => ''));
        exit();
    }

    public function getLinkByIdAction() {
        header('Content-Type: application/json');
        $id = (int) $this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute(NULL, array(
                        'controller' => 'index',
                        'action' => 'list'
            ));
        }
        $tableGateway = $this->getConnection();
        $linkTable = new LinkTable($tableGateway);
        $data = $linkTable->getLinkWebsite($id);

//         Debug::dump($value->url);exit;

        echo json_encode(array('data' => (array) $data));
        exit();
    }

    public function setDateRange() {
        if ($user = $this->identity()) {
            $session = new Container('link');
            $from = $session->offsetGet('from');
            $till = $session->offsetGet('till');
            $website_id = $session->offsetGet('current_website_id');

            $tableGateway = $this->getConnection();
            $linkTable = new LinkTable($tableGateway);
            $website_links_data = $linkTable->dateRange($from, $till, $website_id);
            return $website_links_data;
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
                $all_ranges[] = $parts[2] . '-' . $month . '-' . sprintf("%02s", $day);
            }

            $session = new Container('link');
            $session->offsetSet('current_website_id', $website_id);
            $session->offsetSet('from', $all_ranges[0]);
            $session->offsetSet('till', $all_ranges[1]);
            $session->offsetSet('daterange', $daterange);
            $link_client_id = $session->offsetGet('link_client_id');
            return $this->redirect()->toUrl('/link/index/' . $link_client_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function setmessageAction() {  // set message for delete client link
        if ($user = $this->identity()) {
            $session = new Container('link');
            $link_client_id = $session->offsetGet('link_client_id');
            $website_id = (int) $this->params()->fromRoute('id', 0);
            $session->offsetSet('current_website_id', $website_id);
            $session->offsetSet('msg', "Link has been successfully Deleted.");
//        print_r($website_id);exit;
            return $this->redirect()->toUrl('/link/index/' . $link_client_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function getConnection() {           // set connection to link table
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\Link);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('links', $dbAdapter, null, $resultSetPrototype);
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
