<?php

namespace Clients\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Debug\Debug;
use Zend\Db\Sql\Where;

class BookTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function saveBook(Book $book) {
//                              Debug::dump($book->website);exit;
        $data = array(
            'name' => $book->name,
            'website_id' => $book->website_id,
        );

        $id = (int) $book->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getBook($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Book ID does not exist');
            }
        }
    }

    public function fetchAll() {
        $resultSet = $this->tableGateway->select();
        $resultSet->buffer();
        return $resultSet;
    }

    public function getBook($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getBookWebsite($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('website_id' => $id))->toArray();
//        print_r($rowset);exit;
//        $row = $rowset->current();
//        if (!$rowset) {
//            throw new \Exception("Could not find row $id");
//        }
        return $rowset;
    }

    public function getBookByEmail($bookEmail) {
        $rowset = $this->tableGateway->select(array('email' =>
            $bookEmail));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $bookEmail");
        }
        return $row;
    }

    public function deleteBook($id) {
        $this->tableGateway->delete(array('id' => $id));
    }

    public function deleteBookClient($id) {
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
