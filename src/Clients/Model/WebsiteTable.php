<?php

namespace Clients\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Debug\Debug;

class WebsiteTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function saveWebsite(Website $website) {
//                              Debug::dump($website->website);exit;
        $data = array(
            'website' => $website->website,
            'clients_id' => $website->clients_id,

        );

        $id = (int) $website->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            
        } else {
            if ($this->getWebsite($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Website ID does not exist');
            }
        }
    }
    public function fetchAll() {
        $resultSet = $this->tableGateway->select();
        $resultSet->buffer();
        return $resultSet;
    }

    public function getWebsite($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }
    public function getWebsiteClients($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('clients_id' => $id));
//        print_r($rowset->);exit;
//        $row = $rowset->current();
        if (!$rowset) {
            throw new \Exception("Could not find row $id");
        }
        $rowset->buffer();
        return $rowset;
    }

    public function getWebsiteByEmail($websiteEmail) {
        $rowset = $this->tableGateway->select(array('email' =>
            $websiteEmail));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $websiteEmail");
        }
        return $row;
    }

    public function deleteWebsite($id) {
        $this->tableGateway->delete(array('id' => $id));
    }
      public function deleteWebsiteClient($id) {
        $this->tableGateway->delete(array('clients_id' => $id));
    }

}
