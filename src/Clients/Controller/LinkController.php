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
use Clients\Model\Website;
use Clients\Model\WebsiteTable;
use Clients\Model\Link;
use Clients\Model\LinkTable;
use Clients\Form\AddLinkForm;
use Clients\Form\AddLinkFilter;
use Clients\Form\EditLinkForm;
use Clients\Form\EditLinkFilter;
use Zend\Session\Container;

class LinkController extends AbstractActionController {

    public function indexAction() {
        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);
            $session = new Container('link');
            $session->offsetSet('link_client_id', $id);


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

            if ($session->offsetExists('current_website_id') && $session->offsetGet('current_website_id') != '') {
                $current_website_id = $session->offsetGet('current_website_id');
                if ($session->offsetExists('from') && $session->offsetGet('from') != '') {
                    $current_website_link = $this->setDateRange();
//                print_r($current_website_link);exit;
                } else {
                    $current_website_link = $linkTable->getLinkWebsite($current_website_id);
                }


                if (!empty($current_website_link)) {

                    $viewModel = new ViewModel(array(
                        'client_websites' => $websiteTable->getWebsiteClients($id),
                        'message' => $session->offsetGet('msg'),
                        'website_data' => $current_website_link,
                        'current_website_id' => $current_website_id
                    ));
                } else {
                    $viewModel = new ViewModel(array(
                        'client_websites' => $websiteTable->getWebsiteClients($id),
                        'message' => $session->offsetGet('msg'),
                        'website_data' => $current_website_link,
                        'current_website_id' => $current_website_id
                    ));
                }
            } else {

                $client_websites = $websiteTable->getWebsiteClients($id);
                foreach ($client_websites as $value) {
                    $current_website_id = $value->id;
                    $current_website_link = $linkTable->getLinkWebsite($value->id);
//                 print_r($linkTable->getLinkWebsite($value->id));exit;
                    break;
                }
                $viewModel = new ViewModel(array(
                    'client_websites' => $client_websites,
                    'website_data' => $current_website_link,
                    'current_website_id' => $current_website_id
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


            $viewModel = new ViewModel(array('form' => $form, 'id' => $id));
            return $viewModel;
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
                $all_ranges[] = $parts[2] . '-' . $month . '-' . $day;
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

}
