<?php

namespace Clients\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Debug\Debug;
use Zend\Db\Sql\Where;

class UserRightTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function saveUserRight(UserRight $userRight) {
//                              Debug::dump($userRight->website);exit;
        $data = array(
            'user_id' => $userRight->user_id,
            'crud_user' => $userRight->crud_user,
            'crud_client' => $userRight->crud_client,
            'crud_lead' => $userRight->crud_lead,
            'crud_link' => $userRight->crud_link,
            'crud_traffic' => $userRight->crud_traffic,
            'crud_transcript' => $userRight->crud_transcript,
            'crud_book' => $userRight->crud_book,        
        );

        $id = (int) $userRight->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getUserRight($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('UserRight ID does not exist');
            }
        }
    }

    public function fetchAll() {
        $resultSet = $this->tableGateway->select();
        $resultSet->buffer();
        return $resultSet;
    }

    public function getUserRight($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getUserRightUser($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('user_id' => $id));
         $row = $rowset->current();
//        if (!$row) {
//            throw new \Exception("Could not find row $id");
//        }
        return $row;
//        $rowset->buffer();
//        print_r($rowset);exit;
//        $row = $rowset->current();
//        if (!$rowset) {
//            throw new \Exception("Could not find row $id");
//        }
//        return $rowset;
    }

    public function getUserRightByEmail($userRightEmail) {
        $rowset = $this->tableGateway->select(array('email' =>
            $userRightEmail));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $userRightEmail");
        }
        return $row;
    }

    public function deleteUserRight($id) {
        $this->tableGateway->delete(array('id' => $id));
    }

    public function deleteUserRightClient($id) {
        $this->tableGateway->delete(array('clients_id' => $id));
    }

    public function dateRange($from, $till, $website_id) {
        $where = new Where();
        $where->equalTo('website_id', $website_id);
        $where->between('created_at', $from, $till);
        $resultSet = $this->tableGateway->select($where)->toArray();
//        print_r($resultSet);
//        exit;
//        $resultSet->buffer();
        return $resultSet;
    }

}
