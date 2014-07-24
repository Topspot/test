<?php

namespace Clients\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Debug\Debug;
use Zend\Db\Sql\Where;

class TranscriptTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function saveTranscript(Transcript $transcript) {
//                              Debug::dump($transcript->website);exit;
        $data = array(
            'name' => $transcript->name,
            'date_received' => $transcript->date_received,
            'date_posted' => $transcript->date_posted,
            'date_revised' => $transcript->date_revised,
            'website_id' => $transcript->website_id,
            'fileupload' => $transcript->fileupload,
            'created_at' => date('Y-m-d H:i:s'),
        );

        $id = (int) $transcript->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getTranscript($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Transcript ID does not exist');
            }
        }
    }

    public function fetchAll() {
        $resultSet = $this->tableGateway->select();
        $resultSet->buffer();
        return $resultSet;
    }

    public function getTranscript($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getTranscriptWebsite($id) {
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

    public function getTranscriptByEmail($transcriptEmail) {
        $rowset = $this->tableGateway->select(array('email' =>
            $transcriptEmail));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $transcriptEmail");
        }
        return $row;
    }

    public function deleteTranscript($id) {
        $this->tableGateway->delete(array('id' => $id));
    }

    public function deleteTranscriptClient($id) {
        $this->tableGateway->delete(array('clients_id' => $id));
    }

    public function dateRange($from, $till, $website_id) {
        $where = new Where();
        $where->equalTo('website_id', $website_id);
        $where->between('created_at', $from, $till);
        $resultSet = $this->tableGateway->select($where);
//        print_r($resultSet);
//        exit;
        $resultSet->buffer();
        return $resultSet;
    }

}
