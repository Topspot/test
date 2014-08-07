<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @book      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
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
use Clients\Model\Book;
use Clients\Model\BookTable;
use Clients\Form\AddBookForm;
use Clients\Form\AddBookFilter;
use Clients\Form\EditBookForm;
use Clients\Form\EditBookFilter;
use Zend\Session\Container;
use Clients\Model\UserRightTable;
use Clients\Model\UserRight;
use PHPExcel;
use Excel2007;
use IOFactory;

class BookController extends AbstractActionController {

    public function indexAction() {
        if ($user = $this->identity()) {

            error_reporting(E_ALL);
            ini_set('display_errors', '1');
            //get current user data
            $auth = new AuthenticationService();
            $user_data = $auth->getIdentity();

            $id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('book');
            $session->offsetSet('book_client_id', $id);


            if (!$id) {
                return $this->redirect()->toRoute(NULL, array(
                            'controller' => 'index',
                            'action' => 'list'
                ));
            }
            $tableGatewayWebsite = $this->getConnectionWebsite();
            $websiteTable = new WebsiteTable($tableGatewayWebsite);

            $tableGateway = $this->getConnection();
            $bookTable = new BookTable($tableGateway);

            $websiteTable = new WebsiteTable($tableGatewayWebsite);
            $tableGatewayUserRights = $this->getConnectionUserRights();
            $UserRight = new UserRightTable($tableGatewayUserRights);
            if ($auth->getIdentity()->roles_id == 2) {
                $applying_user_rights = $UserRight->getUserRightUser($user_data->usr_id);
            } else {
                $applying_user_rights = '';
            }
            if ($session->offsetExists('current_website_id') && $session->offsetGet('current_website_id') != '') {
                $current_website_id = $session->offsetGet('current_website_id');
                if ($session->offsetExists('from') && $session->offsetGet('from') != '') {
                    $current_website_book = $this->setDateRange();
                } else {

                    $current_website_book = $bookTable->getBookWebsite($current_website_id);
                }


                if (!empty($current_website_book)) {

                    $viewModel = new ViewModel(array(
                        'client_websites' => $websiteTable->getWebsiteClients($id),
                        'message' => $session->offsetGet('msg'),
                        'website_data' => $current_website_book,
                        'current_website_id' => $current_website_id,
                        'applying_user_rights' => $applying_user_rights
                    ));
                } else {
                    $viewModel = new ViewModel(array(
                        'client_websites' => $websiteTable->getWebsiteClients($id),
                        'message' => $session->offsetGet('msg'),
                        'website_data' => $current_website_book,
                        'current_website_id' => $current_website_id,
                        'applying_user_rights' => $applying_user_rights
                    ));
                }
            } else {

                $client_websites = $websiteTable->getWebsiteClients($id);

                foreach ($client_websites as $value) {
                    $current_website_id = $value->id;
                    $current_website_book = $bookTable->getBookWebsite($value->id);
                    break;
                }

                $viewModel = new ViewModel(array(
                    'client_websites' => $client_websites,
                    'website_data' => $current_website_book,
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

            $session = new Container('book');
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
                    ->setCellValue('A1', 'Name');

            $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->setBold(true);
            for ($i = 0; $i <= $num; $i++) {
                $data = $session->offsetGet('leadobject' . $i);
//                print_r($data->name);
                $cell = $i + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $cell, $data->name);
            }
//            exit;
// Rename worksheet
            $objPHPExcel->getActiveSheet()->setTitle('Books');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a clientâ€™s web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="books.xlsx"');
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
            $session = new Container('book');
            $book_client_id = $session->offsetGet('book_client_id');
            $session->offsetSet('current_website_id', $id);

            if (!$id) {
                return $this->redirect()->toRoute(NULL, array(
//                        'controller' => 'book',
                            'action' => 'index',
                            'id' => $book_client_id
                ));
            }
            $form = new AddBookForm();
            if ($this->request->isPost()) {
                $tableGateway = $this->getConnection();
                $post = $this->request->getPost();
                $post->website_id = $id;

                $book = new Book();
                $book->exchangeArray($post);
                $bookTable = new BookTable($tableGateway);

                $id = $bookTable->saveBook($book);
                $session->offsetSet('msg', "Book has been successfully Added.");
                return $this->redirect()->toUrl('/book/index/' . $book_client_id);
            }
            $viewModel = new ViewModel(array('form' => $form, 'id' => $id,'book_client_id' => $book_client_id));
            return $viewModel;
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function changewebsiteAction() {
        if ($user = $this->identity()) {
            $website_id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('book');
            $book_client_id = $session->offsetGet('book_client_id');
            $session->offsetSet('current_website_id', $website_id);
            $session->offsetSet('msg', "");
            return $this->redirect()->toUrl('/book/index/' . $book_client_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function editAction() {
        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('book');
            $book_client_id = $session->offsetGet('book_client_id');
            $session->offsetSet('msg', "Book has been successfully Updated.");
            if (!$id) {
                return $this->redirect()->toRoute(NULL, array(
                            'controller' => 'index',
                            'action' => 'add'
                ));
            }
            $tableGateway = $this->getConnection();
            $bookTable = new BookTable($tableGateway);
            $form = new EditBookForm();
            if ($this->request->isPost()) {
                $post = $this->request->getPost();
                //saving Client data table
                $book = $bookTable->getBook($post->id);
                $form->bind($book);
                $form->setData($post);
                $book->name = $post->name;
                $session->offsetSet('current_website_id', $book->website_id);
                $bookTable->saveBook($book);
                return $this->redirect()->toUrl('/book/index/' . $book_client_id);
            }
            $book = $bookTable->getBook($this->params()->fromRoute('id'));

            $form->bind($book); //biding data to form
            $viewModel = new ViewModel(array(
                'form' => $form,
                'id' => $this->params()->fromRoute('id'),
                'book_client_id' => $book_client_id
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
        //delete Book for a client website
        $tableGateway = $this->getConnection();
        $bookTable = new BookTable($tableGateway);
        $bookTable->deleteBook($id);
        echo json_encode(array('data' => ''));
        exit();
    }

    public function getBookByIdAction() {
        header('Content-Type: application/json');
        $id = (int) $this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute(NULL, array(
                        'controller' => 'index',
                        'action' => 'list'
            ));
        }
        $tableGateway = $this->getConnection();
        $bookTable = new BookTable($tableGateway);
        $data = $bookTable->getBookWebsite($id);

        echo json_encode(array('data' => (array) $data));
        exit();
    }

    public function setDateRange() {
        if ($user = $this->identity()) {
            $session = new Container('book');
            $from = $session->offsetGet('from');
            $till = $session->offsetGet('till');
            $website_id = $session->offsetGet('current_website_id');

            $tableGateway = $this->getConnection();
            $bookTable = new BookTable($tableGateway);
            $website_books_data = $bookTable->dateRange($from, $till, $website_id);
            return $website_books_data;
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

            $session = new Container('book');
            $session->offsetSet('current_website_id', $website_id);
            $session->offsetSet('from', $all_ranges[0]);
            $session->offsetSet('till', $all_ranges[1]);
            $session->offsetSet('daterange', $daterange);
            $book_client_id = $session->offsetGet('book_client_id');
            return $this->redirect()->toUrl('/book/index/' . $book_client_id);
        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function setmessageAction() {  // set message for delete client book
        $session = new Container('book');
        $book_client_id = $session->offsetGet('book_client_id');
        $website_id = (int) $this->params()->fromRoute('id', 0);
        $session->offsetSet('current_website_id', $website_id);
        $session->offsetSet('msg', "Book has been successfully Deleted.");
//        print_r($website_id);exit;
        return $this->redirect()->toUrl('/book/index/' . $book_client_id);
    }

    public function getConnection() {           // set connection to book table
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\Book);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('books', $dbAdapter, null, $resultSetPrototype);
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
