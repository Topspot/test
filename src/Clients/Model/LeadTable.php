<?php

namespace Clients\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Debug\Debug;
use Zend\Db\Sql\Where;
use Zend\Authentication\AuthenticationService;

class LeadTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    public function saveLead(Lead $lead) {
        $auth = new AuthenticationService();
        if ($auth->getIdentity()->roles_id == 2) {
            $data = array(
                'caller_type' => $lead->caller_type,
                'lead_name' => $lead->lead_name,
                'lead_email' => $lead->lead_email,
                'website_id' => $lead->website_id,
            );
        } else {
            $data = array(
                'caller_type' => $lead->caller_type,
                'lead_date' => $lead->lead_date,
                'lead_source' => $lead->lead_source,
                'inc_phone' => $lead->inc_phone,
                'call_time' => $lead->call_time,
                'call_duration' => $lead->call_duration,
                'lead_name' => $lead->lead_name,
                'lead_email' => $lead->lead_email,
                'website_id' => $lead->website_id,
                'client_name' => $lead->client_name,
                'website' => $lead->website,
            );
        }
        $id = (int) $lead->id;
//         Debug::dump($id);
//         Debug::dump($lead->id);exit;
        if ($id == 0 || $id == '') {
            $this->tableGateway->insert($data);

            $id = $this->tableGateway->lastInsertValue;
            return $id;
        } else {
//                      Debug::dump($id);exit;
            if ($this->getLead($id)) {

                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Lead ID does not exist');
            }
        }
    }

    public function fetchAll() {
        $resultSet = $this->tableGateway->select();
        $resultSet->buffer();
        return $resultSet;
    }

    public function getLead($id) {
        $id = (int) $id;

        $rowset = $this->tableGateway->select(array('id' => $id));

        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }

        return $row;
    }

    public function getLeadWebsite($id) {
        $id = (int) $id;
//        print_r($id);
        $rowset = $this->tableGateway->select(array('website_id' => $id));
//        foreach($rowset as $row){
//            print_r($row);
//                        exit();
//        }
         $rowset->buffer();
        return $rowset;
    }

    public function getLeadByEmail($leadEmail) {
        $rowset = $this->tableGateway->select(array('email' =>
            $leadEmail));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $leadEmail");
        }
        return $row;
    }

    public function deleteLead($id) {
        $this->tableGateway->delete(array('id' => $id));
    }

    public function dateRange($from, $till, $website_id) {
//        print_r("daterange");
        $where = new Where();
        $where->equalTo('website_id', $website_id);
        $where->between('lead_date', $from, $till);
        $resultSet = $this->tableGateway->select($where);
        $resultSet->buffer();
//        print_r($resultSet);
//        exit;
//        $resultSet->buffer();
        return $resultSet;
    }
        public function alldateRange($from, $till) {
        $where = new Where();
        $where->between('lead_date', $from, $till);
        $resultSet = $this->tableGateway->select($where);

        $resultSet->buffer();
        return $resultSet;
    }

}
