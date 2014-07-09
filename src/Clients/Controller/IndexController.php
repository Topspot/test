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

class IndexController extends AbstractActionController {

    public function indexAction() {
//        $tableGateway=$this->getConnection();
//        $clientTable = new ClientTable($tableGateway);
//        $viewModel = new ViewModel(array('users' => $clientTable->fetchAll()));
        return new ViewModel();
    }

    public function listAction() {
        
     if ($user = $this->identity()) {
         echo 'Logged in as';
     } else {
         echo 'Not logged in';
     }
        $session = new Container('link');
        $delete_msg = $session->offsetGet('delete_user_msg');
        $tableGateway = $this->getConnection();
        $clientTable = new ClientTable($tableGateway);
        $tableGatewayWebsite = $this->getConnectionWebsite();
        $websiteTable = new WebsiteTable($tableGatewayWebsite);

        if (isset($delete_msg) && $delete_msg != '') {
            $viewModel = new ViewModel(array(
                'clients' => $clientTable->fetchAll(),
                'websites' => $websiteTable->fetchAll(),
                'message' => $delete_msg
            ));
        } else {
            $viewModel = new ViewModel(array(
                'clients' => $clientTable->fetchAll(),
                'websites' => $websiteTable->fetchAll()
            ));
        }

        return $viewModel;
    }

    public function addAction() {
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
            $session->offsetSet('delete_user_msg', "User has been Created");
            return $this->redirect()->toRoute(NULL, array(
                        'controller' => 'index',
                        'action' => 'list'
            ));
        }


        $viewModel = new ViewModel(array('form' => $form));
        return $viewModel;
    }

    // insert multiple website
    public function insertWebsiteAction($client_website, $id, $websiteTable) {
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
    }

    public function editAction() {
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
            $session->offsetSet('delete_user_msg', "User has been Updated");
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

        $session = new Container('link');
        $session->offsetSet('delete_user_msg', "User has been Deleted");

        return $this->redirect()->toRoute(NULL, array(
                    'controller' => 'index',
                    'action' => 'list'
        ));
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

}
