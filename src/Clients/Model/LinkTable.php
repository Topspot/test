<?php

namespace Clients\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Debug\Debug;
use Zend\Db\Sql\Where;

class LinkTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function saveLink(Link $link) {
//                              Debug::dump($link->website);exit;
        $data = array(
            'date' => $link->date,
            'url' => $link->url,
            'website_id' => $link->website_id,
        );

        $id = (int) $link->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getLink($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Link ID does not exist');
            }
        }
    }

    public function fetchAll() {
        $resultSet = $this->tableGateway->select();
        $resultSet->buffer();
        return $resultSet;
    }

    public function getLink($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getLinkWebsite($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('website_id' => $id));
         $rowset->buffer();
//        print_r($rowset);exit;
//        $row = $rowset->current();
//        if (!$rowset) {
//            throw new \Exception("Could not find row $id");
//        }
        return $rowset;
    }

    public function getLinkByEmail($linkEmail) {
        $rowset = $this->tableGateway->select(array('email' =>
            $linkEmail));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $linkEmail");
        }
        return $row;
    }

    public function deleteLink($id) {
        $this->tableGateway->delete(array('id' => $id));
    }

    public function deleteLinkClient($id) {
        $this->tableGateway->delete(array('clients_id' => $id));
    }

    public function dateRange($from, $till, $website_id) {
        $where = new Where();
        $where->equalTo('website_id', $website_id);
        $where->between('date', $from, $till);
//        $where->greaterThanOrEqualTo('date', $from);
//        $where->lessThanOrEqualTo('date', $till);
        $resultSet = $this->tableGateway->select($where)->toArray();
//        print_r($resultSet);
//        exit;
//        $resultSet->buffer();
        return $resultSet;
    }

}
