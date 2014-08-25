<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @transcript      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Clients\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationService;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;
use Zend\Filter\Compress;
use Clients\Model\Website;
use Clients\Model\WebsiteTable;
use Clients\Model\Transcript;
use Clients\Model\TranscriptTable;
use Clients\Form\AddTranscriptForm;
use Clients\Form\AddTranscriptFilter;
use Clients\Form\EditTranscriptForm;
use Clients\Form\EditTranscriptFilter;
use Zend\Session\Container;
use PHPExcel;
use Excel2007;
use IOFactory;
use Clients\Model\UserRightTable;
use Clients\Model\UserRight;

class TranscriptController extends AbstractActionController {

    public function indexAction() {
        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('transcript');
            $session->offsetSet('transcript_client_id', $id);
            //get current user data
            $auth = new AuthenticationService();
            $user_data = $auth->getIdentity();

            if (!$id) {
                return $this->redirect()->toRoute(NULL, array(
                            'controller' => 'index',
                            'action' => 'list'
                ));
            }
            $tableGatewayWebsite = $this->getConnectionWebsite();
            $websiteTable = new WebsiteTable($tableGatewayWebsite);

            $tableGateway = $this->getConnection();
            $transcriptTable = new TranscriptTable($tableGateway);

            $tableGatewayUserRights = $this->getConnectionUserRights();
            $UserRight = new UserRightTable($tableGatewayUserRights);
            if ($auth->getIdentity()->roles_id == 2) {
                $applying_user_rights = $UserRight->getUserRightUser($user_data->usr_id);
            } else {
                $applying_user_rights = '';
            }
            $client_websites = $websiteTable->getWebsiteClients($id);

            foreach ($client_websites as $value) {
                $current_website_idd = $value->id;
                $current_website_transcriptt = $transcriptTable->getTranscriptWebsite($value->id);
                break;
            }

            $session_daterange = new Container('daterange');
            if (isset($_GET['cws_id']) && !empty($_GET['cws_id'])) {
                $cws_id = $_GET['cws_id'];
                $session->offsetSet('current_website_id', $cws_id);
            } else {
                if ($session->offsetGet('check_website_id') == "yes") {
                    
                } else {
                    $session->offsetSet('current_website_id', $current_website_idd);
                }
            }
//              print_r("set surrent website id".$current_website_idd);
//            print_r($session->offsetGet('current_website_id'));
            if ($session->offsetExists('current_website_id') && $session->offsetGet('current_website_id') != '') {
//                  print_r("second");
                $current_website_id = $session->offsetGet('current_website_id');
                if ($session_daterange->offsetExists('from') && $session_daterange->offsetGet('from') != '') {
                    $current_website_transcript = $this->setDateRange();
//                     print_r("inner");
//                print_r($current_website_transcript);exit;
                } else {
                    $current_website_transcript = $transcriptTable->getTranscriptWebsite($current_website_id);
//                      print_r("outer");
                }
                if (!empty($current_website_transcript)) {

                    $viewModel = new ViewModel(array(
                        'client_websites' => $client_websites,
                        'message' => $session->offsetGet('msg'),
                        'website_data' => $current_website_transcript,
                        'current_website_id' => $current_website_id,
                        'applying_user_rights' => $applying_user_rights
                    ));
                } else {
                    $viewModel = new ViewModel(array(
                        'client_websites' => $client_websites,
                        'message' => $session->offsetGet('msg'),
                        'website_data' => $current_website_transcript,
                        'current_website_id' => $current_website_id,
                        'applying_user_rights' => $applying_user_rights
                    ));
                }
            } else {
                $session->offsetSet('daterange', '');
                $viewModel = new ViewModel(array(
                    'client_websites' => $client_websites,
                    'website_data' => $current_website_transcriptt,
                    'current_website_id' => $current_website_idd,
                    'applying_user_rights' => $applying_user_rights
                ));
            }

            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function setDateRange() {
        if ($user = $this->identity()) {
            $session = new Container('transcript');
            $session_daterange = new Container('daterange');
            $from = $session_daterange->offsetGet('from');
            $till = $session_daterange->offsetGet('till');
            $website_id = $session->offsetGet('current_website_id');
            $from = $from . ' 00:00:00';
            $till = $till . ' 23:59:59';
            $tableGateway = $this->getConnection();
            $transcriptTable = new TranscriptTable($tableGateway);
            $website_transcripts_data = $transcriptTable->dateRange($from, $till, $website_id);
            return $website_transcripts_data;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function daterangeAction() {      // finding daterange data from database
        if ($user = $this->identity()) {
            $daterange = $_GET['daterange'];
            $current_client_id = $_GET['client_id'];
            $current_website_id = $_GET['current_website_id'];

            $ranges = explode('-', $daterange);
            $all_ranges = array();
            foreach ($ranges as $range) {
                $range = trim($range);
                $parts = explode(' ', $range);
                $month = date("m", strtotime($parts[0]));
                $day = rtrim($parts[1], ',');
                $all_ranges[] = $parts[2] . '-' . $month . '-' . sprintf("%02s", $day);
            }
            $session = new Container('transcript');
            $session->offsetSet('current_website_id', $current_website_id);
            $session->offsetSet('from', $all_ranges[0]);
            $session->offsetSet('till', $all_ranges[1]);
            $session->offsetSet('daterange', $daterange);
            $session->offsetSet('check_website_id', "yes");
            $session_daterange = new Container('daterange');
            $session_daterange->offsetSet('daterange', $daterange);
            $session_daterange->offsetSet('from', $all_ranges[0]);
            $session_daterange->offsetSet('till', $all_ranges[1]);
            $transcript_client_id = $session->offsetGet('transcript_client_id');
            return $this->redirect()->toUrl('/transcript/index/' . $transcript_client_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function setmessageAction() {  // set message for delete client transcript
        if ($user = $this->identity()) {
            $session = new Container('transcript');
            $transcript_client_id = $session->offsetGet('transcript_client_id');
            $website_id = (int) $this->params()->fromRoute('id', 0);
            $session->offsetSet('current_website_id', $website_id);
            $session->offsetSet('msg', "Transcript has been successfully Deleted.");
            $session->offsetSet('check_website_id', "yes");
//        print_r($website_id);exit;
            return $this->redirect()->toUrl('/transcript/index/' . $transcript_client_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function exportdataAction() {
        if ($user = $this->identity()) {
            $num = (int) $this->params()->fromRoute('id', 0);

            $session = new Container('transcript');
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
                    ->setCellValue('A1', 'Name')
                    ->setCellValue('B1', 'Date Recevied')
                    ->setCellValue('C1', 'Date Posted')
                    ->setCellValue('D1', 'Date Revised');

            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);
            for ($i = 0; $i <= $num; $i++) {
                $data = $session->offsetGet('leadobject' . $i);
                $cell = $i + 2;
                $originalDate = $data->date_posted;
                $date_posted = date("m-d-Y", strtotime($originalDate));
                $originalDate = '';
                $originalDate = $data->date_received;
                $date_received = date("m-d-Y", strtotime($originalDate));
                $originalDate = '';
                $originalDate = $data->date_revised;
                $date_revised = date("m-d-Y", strtotime($originalDate));

                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $cell, $data->name)
                        ->setCellValue('B' . $cell, $date_posted)
                        ->setCellValue('C' . $cell, $date_received)
                        ->setCellValue('D' . $cell, $date_revised);
            }
// Rename worksheet
            $objPHPExcel->getActiveSheet()->setTitle('Transcripts');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a clientâ€™s web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="transcripts.xlsx"');
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
            $session = new Container('transcript');
            $transcript_client_id = $session->offsetGet('transcript_client_id');
            $session->offsetSet('current_website_id', $id);

            if (!$id) {
                return $this->redirect()->toRoute(NULL, array(
//                        'controller' => 'transcript',
                            'action' => 'index',
                            'id' => $transcript_client_id
                ));
            }
            $form = new AddTranscriptForm();
            if ($this->request->isPost()) {

                $post = $this->request->getPost();
                $adapter = new \Zend\File\Transfer\Adapter\Http();
                $uploadFile = $this->params()->fromFiles('fileupload');
                $post = array_merge_recursive(
                        $this->request->getPost()->toArray(), array('fileupload' => $uploadFile['name'])
                );

                $uploadPath = getcwd() . '/module/Clients/data/uploads//' . $id;
                print_r($uploadPath);
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true); //                    
                }
                $adapter->setDestination($uploadPath);
                if ($adapter->receive($uploadFile['name'])) {
                    $post['website_id'] = $id;
                    $post['date_posted'] = date("Y-m-d", strtotime($post['date_posted']));
                    $post['date_received'] = date("Y-m-d", strtotime($post['date_received']));
                    $post['date_revised'] = date("Y-m-d", strtotime($post['date_revised']));

                    $transcript = new Transcript();
                    $transcript->exchangeArray($post);
                    $tableGateway = $this->getConnection();
                    $transcriptTable = new TranscriptTable($tableGateway);

                    $id = $transcriptTable->saveTranscript($transcript);
                    $session->offsetSet('msg', "Transcript has been successfully Added.");
                    $session->offsetSet('check_website_id', "yes");
                    return $this->redirect()->toUrl('/transcript/index/' . $transcript_client_id);
                } else {
                    print_r("Could not get file in uploads folder");
                    exit();
                }
            }
            $viewModel = new ViewModel(array('form' => $form, 'id' => $id, 'transcript_client_id' => $transcript_client_id));
            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function changewebsiteAction() {
        if ($user = $this->identity()) {
            $website_id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('transcript');
            $transcript_client_id = $session->offsetGet('transcript_client_id');
            $session->offsetSet('current_website_id', $website_id);
            $session->offsetSet('msg', "");
            $session->offsetSet('check_website_id', "yes");
            return $this->redirect()->toUrl('/transcript/index/' . $transcript_client_id. '?cws_id=' . $website_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function editAction() {
        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('transcript');
            $transcript_client_id = $session->offsetGet('transcript_client_id');
            $session->offsetSet('msg', "Transcript has been successfully Updated.");
            if (!$id) {
                return $this->redirect()->toRoute(NULL, array(
                            'controller' => 'index',
                            'action' => 'add'
                ));
            }
            $tableGateway = $this->getConnection();
            $transcriptTable = new TranscriptTable($tableGateway);
            $transcript = $transcriptTable->getTranscript($this->params()->fromRoute('id'));
            $file_name = $transcript->fileupload;
            $form = new EditTranscriptForm();
            if ($this->request->isPost()) {
                $uploadFile = $this->params()->fromFiles('fileupload');
                $adapter = new \Zend\File\Transfer\Adapter\Http();
                if ($uploadFile['name'] == '') {
                    $post = array_merge_recursive(
                            $this->request->getPost()->toArray(), array('fileupload' => $file_name)
                    );
                    //saving Client data table
                    $transcript = $transcriptTable->getTranscript($post['id']);
                    $form->bind($transcript);
                    $form->setData($post);
                    $post['date_posted'] = date("Y-m-d", strtotime($post['date_posted']));
                    $post['date_received'] = date("Y-m-d", strtotime($post['date_received']));
                    $post['date_revised'] = date("Y-m-d", strtotime($post['date_revised']));
                    $transcript->date_posted = $post['date_posted'];
                    $transcript->date_revised = $post['date_revised'];
                    $transcript->date_received = $post['date_received'];
                    $transcript->name = $post['name'];
                    $transcript->fileupload = $post['fileupload'];
                    $session->offsetSet('current_website_id', $transcript->website_id);
                    $transcriptTable->saveTranscript($transcript);    // updating the data
                    return $this->redirect()->toUrl('/transcript/index/' . $transcript_client_id);
                } else {
                    $filename = getcwd() . '/module/Clients/data/uploads//' . $transcript->website_id . '//' . $file_name;
                    unlink($filename);        // delete the old uploaded files
                    // upload new file
                    $uploadPath = getcwd() . '/module/Clients/data/uploads//' . $transcript->website_id;

                    $post = array_merge_recursive(
                            $this->request->getPost()->toArray(), array('fileupload' => $uploadFile['name'])
                    );

                    $adapter->setDestination($uploadPath);
                    if ($adapter->receive($uploadFile['name'])) {   //if file is received in uploaded folder
                        //saving Client data table
                        $transcript = $transcriptTable->getTranscript($post['id']);
                        $form->bind($transcript);
                        $form->setData($post);

                        $post['date_posted'] = date("Y-m-d", strtotime($post['date_posted']));
                        $post['date_received'] = date("Y-m-d", strtotime($post['date_received']));
                        $post['date_revised'] = date("Y-m-d", strtotime($post['date_revised']));
                        $transcript->date_posted = $post['date_posted'];
                        $transcript->date_revised = $post['date_revised'];
                        $transcript->date_received = $post['date_received'];
                        $transcript->name = $post['name'];
                        $transcript->fileupload = $post['fileupload'];
                        $session->offsetSet('current_website_id', $transcript->website_id);
                        $session->offsetSet('check_website_id', "yes");
                        $transcriptTable->saveTranscript($transcript);    // updating the data
                        return $this->redirect()->toUrl('/transcript/index/' . $transcript_client_id);
                    } else {
                        print_r("Could not get file in uploads folder");
                        exit();
                    }
                }
            }
            // changing date formation
            $transcript->date_posted = date("m/d/Y", strtotime($transcript->date_posted));
            $transcript->date_received = date("m/d/Y", strtotime($transcript->date_received));
            $transcript->date_revised = date("m/d/Y", strtotime($transcript->date_revised));
            $form->bind($transcript); //biding data to form

            $viewModel = new ViewModel(array(
                'form' => $form,
                'id' => $this->params()->fromRoute('id'),
                'fileupload' => $transcript->fileupload,
                'transcript_client_id' => $transcript_client_id
            ));
            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function deleteAction() {  // delete transcript
        header('Content-Type: application/json');
        $current_website_id = $_POST['current_website'];
//            print_r($_POST['current_website']);exit;
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute(NULL, array(
                        'controller' => 'index',
                        'action' => 'list'
            ));
        }
        //delete Transcript for a client website
        $tableGateway = $this->getConnection();
        $transcriptTable = new TranscriptTable($tableGateway);
        $data = $transcriptTable->getTranscript($id);
        ini_set("display_errors", "1");
        error_reporting(E_ALL & ~E_NOTICE);
        $filename = getcwd() . '/module/Clients/data/uploads//' . $current_website_id . '//' . $data->fileupload;
//        print_r($filename);exit;
        unlink($filename);        // delete the old uploaded files
        $transcriptTable->deleteTranscript($id);
        echo json_encode(array('data' => ''));
        exit();
    }

    public function downloadallAction() {
        if ($user = $this->identity()) {
            $download_ids = $_POST['downloadids'];
            $current_website_id = (int) $this->params()->fromRoute('id', 0);
            if (!$current_website_id) {
                print_r("Cant get id in download ALL action");
                exit();
            }
            $tableGateway = $this->getConnection();
            $transcriptTable = new TranscriptTable($tableGateway);
            if (!file_exists(getcwd() . '/module/Clients/data/uploads/temp')) {
                mkdir(getcwd() . '/module/Clients/data/uploads/temp', 0777, true);
            }
            if (!empty($download_ids)) {
                $download_ids = explode(",", $download_ids);
                foreach ($download_ids as $ids) {
                    $single_data = $transcriptTable->getTranscript($ids);
                    $filename = getcwd() . '/module/Clients/data/uploads//' . $current_website_id . '//' . $single_data->fileupload;
                    $filename1 = getcwd() . '/module/Clients/data/uploads/temp//' . $single_data->fileupload;
                    copy($filename, $filename1);
                }
            } else {
                $data = $transcriptTable->getTranscriptWebsite($current_website_id);
                foreach ($data as $value) {
                    $filename = getcwd() . '/module/Clients/data/uploads//' . $current_website_id . '//' . $value->fileupload;
                    $filename1 = getcwd() . '/module/Clients/data/uploads/temp//' . $value->fileupload;
                    copy($filename, $filename1);
                }
            }
            $filter = new \Zend\Filter\Compress(array(
                'adapter' => 'Zip',
                'options' => array(
                    'archive' => 'transcript.zip'
                ),
            ));
            $compressed = $filter->filter(getcwd() . '/module/Clients/data/uploads/temp');

            $files = glob(getcwd() . '/module/Clients/data/uploads/temp/*'); // get all file names
            foreach ($files as $file) { // iterate files
                if (is_file($file))
                    unlink($file); // delete file
            }
            if (is_dir(getcwd() . '/module/Clients/data/uploads/temp')) {
                if (!rmdir(getcwd() . '/module/Clients/data/uploads/temp')) { {
                        echo ("Could not remove");
                        exit;
                    }
                }
            }
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=transcript.zip');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($compressed)); // $file));
            ob_clean();
            flush();
            // readfile($file);
            readfile($compressed);
            //        print_r($files);exit;
            exit;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function downloadAction() {
        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('transcript');
            $current_website_id = $session->offsetGet('transcript_website_id');
            if (!$id) {
                print_r("Cant get id in download action");
                exit();
            }
            $tableGateway = $this->getConnection();
            $transcriptTable = new TranscriptTable($tableGateway);
            $data = $transcriptTable->getTranscript($id);
            $filename = getcwd() . '/module/Clients/data/uploads//' . $current_website_id . '//' . $data->fileupload;
            if (file_exists($filename)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . basename($data->fileupload));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filename)); // $file));
                ob_clean();
                flush();
                // readfile($file);
                readfile($filename);
                exit;
            }
            return new ViewModel(array());
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function getTranscriptByIdAction() {
        header('Content-Type: application/json');
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute(NULL, array(
                        'controller' => 'index',
                        'action' => 'list'
            ));
        }
        $tableGateway = $this->getConnection();
        $transcriptTable = new TranscriptTable($tableGateway);
        $data = $transcriptTable->getTranscriptWebsite($id);
        echo json_encode(array('data' => (array) $data));
        exit();
    }

    public function getConnection() {           // set connection to transcript table
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\Transcript);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('transcripts', $dbAdapter, null, $resultSetPrototype);
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
