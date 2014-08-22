<?php

namespace Clients\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Debug\Debug;
use Zend\Db\Sql\Select;

class ClientTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function saveClient(Client $client) {
//                              Debug::dump($client);exit;
        $data = array(
            'email' => $client->email,
            'name' => $client->name,
//            'website' => $client->website,
            'phone' => $client->phone,
            'calltracking' => $client->calltracking,
        );

        $id = (int) $client->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            
            $id = $this->tableGateway->lastInsertValue;
//            $id->buffer();
//            $id->next()();
            return $id;
        } else {
            if ($this->getClient($id)) {
//                                                                                       echo '<pre>';
//                        print_r($data);
//                        echo '</pre>';
//                        exit;
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Client ID does not exist');
            }
        }
    }
    public function fetchAll() {
        $resultSet = $this->tableGateway->select();
         $resultSet->buffer();
        return $resultSet;
    }
    public function ascfetchAll() {
        $resultSet = $this->tableGateway->select(function (Select $select) {;
            $select->order('name ASC');
       });
//         $resultSet->buffer();
        return $resultSet;
    }

    public function getClient($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getClientByEmail($clientEmail) {
        $rowset = $this->tableGateway->select(array('email' =>
            $clientEmail));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $clientEmail");
        }
        return $row;
    }

    public function deleteClient($id) {
        $this->tableGateway->delete(array('id' => $id));
    }

}
