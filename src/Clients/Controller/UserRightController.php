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
use Clients\Model\UserRight;
use Clients\Model\UserRightTable;
use Clients\Form\EditUserRightForm;
use Clients\Form\EditUserRightFilter;
use Auth\Model\UsersTable;
use Auth\Model\Auth;

class UserRightController extends AbstractActionController {

    public function indexAction() {
        if ($user = $this->identity()) {
            $id = (int) $this->params()->fromRoute('id', 0);

            $tableGateway = $this->getConnection();
            $userRightTable = new UserRightTable($tableGateway);

            $all_userRight_data = $userRightTable->fetchAll();

            $usertableGateway = $this->getUserConnection();
            $usersTable = new UsersTable($usertableGateway);

            $user_name = array();
            $inc = 0;
            foreach ($all_userRight_data as $userright) {
                $user_name[$inc]['id'] = $userright->user_id;

                $user_name_data = $usersTable->getUsername($userright->user_id);
                $user_name[$inc]['name'] = $user_name_data->usr_name;
                $inc = $inc + 1;
            }
            if ($id == 0) {
                $viewModel = new ViewModel(array(
                    'user_data' => $user_name,
                ));
                return $viewModel;
            } else {
                $user_rights_data = $userRightTable->getUserRightUser($id);
                $form = new EditUserRightForm();
                $form->bind($user_rights_data);
                if ($this->request->isPost()) {
                   $post = $this->request->getPost();
//                   print_r($post->crud_book);exit;
                //saving Client data table
                $userRight = $userRightTable->getUserRight($post->id);

                
                $userRight->user_id=$id;
                $userRight->crud_user=$post->crud_user;
                $userRight->crud_client=$post->crud_client;
                $userRight->crud_lead=$post->crud_lead;
                $userRight->crud_link=$post->crud_link;
                $userRight->crud_traffic=$post->crud_traffic;
                $userRight->crud_transcript=$post->crud_transcript;
                $userRight->crud_book=$post->crud_book;
                $form->bind($userRight);
                $userRightTable->saveUserRight($userRight);
                $viewModel = new ViewModel(array(
                    'form' => $form,
                    'id' => $this->params()->fromRoute('id'),
                    'user_data' => $user_name,
                    'message' => "User Rights has been changed.",
                ));
                return $viewModel;    
                }
                
                $viewModel = new ViewModel(array(
                    'form' => $form,
                    'id' => $this->params()->fromRoute('id'),
                    'user_data' => $user_name,
                ));
                return $viewModel;
            }
            echo "out";
            exit;

        } else {
            return $this->redirect()->toUrl('/auth/index/login'); //redirect from one module to another
        }
    }

    public function getConnection() {
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Clients\Model\UserRight);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('user_rights', $dbAdapter, null, $resultSetPrototype);
        return $tableGateway;
    }

    public function getUserConnection() {
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new
                \Auth\Model\Auth);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('users', $dbAdapter, null, $resultSetPrototype);
        return $tableGateway;
    }

}
