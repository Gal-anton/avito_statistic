<?php

namespace Src\Controller;

use Src\TableGateways\StatisticGateway;

class StatisticController
{

    private $db;
    private $requestMethod;

    private StatisticGateway $statisticGateway;

    public function __construct($db, $requestMethod)
    {
        $this->db            = $db;
        $this->requestMethod = $requestMethod;

        $this->statisticGateway = new StatisticGateway($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                $response = $this->getStatistics();
                break;
            case 'POST':
                $response = $this->createStatisticsFromRequest();
                break;
            case 'DELETE':
                $response = $this->deleteStatistics();
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    /**
     * @return array
     */
    private function getStatistics()
    {
        $input = (array) json_decode(file_get_contents('php://input'), true);

        $order = $input["order"] ?? "date";
        $validOrders = ["date", "views", "clicks", "cost", "cpc", "cpm"];
        if (!array_key_exists("from", $input) ||
            !array_key_exists("to", $input) ||
            !$this->validateDate($input["from"]) ||
            !$this->validateDate($input["to"]) ||
            !in_array($order, $validOrders))
        {
            return $this->unprocessableEntityResponse();
        }
        $result                         = $this->statisticGateway->findAll(
                                                                            $input["from"],
                                                                            $input["to"],
                                                                            $order);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body']               = json_encode($result);

        return $response;
    }

    private function createStatisticsFromRequest()
    {
        $input = (array) json_decode(file_get_contents('php://input'), true);
        if (!$this->validateStatistic($input)) {
            return $this->unprocessableEntityResponse();
        }

        $this->statisticGateway->insert($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body']               = null;

        return $response;
    }


    private function deleteStatistics()
    {
        $this->statisticGateway->delete();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body']               = null;

        return $response;
    }

    private function validateStatistic($input)
    {

        if (!$this->validateDate($input["date"])) {
            return false;
        }

        if (isset($input['views']) && !is_int($input['views'])) {
            return false;
        }

        if (isset($input['clicks']) && !is_int($input['clicks'])) {
            return false;
        }

        if (isset($input['cost']) && !is_float($input['cost'])) {
            return false;
        }

        return true;
    }

    private function validateDate($date) {
        $dataValidate = explode('-', $date);
        if (count($dataValidate) !== 3 ||
            !checkdate($dataValidate[1], $dataValidate[2], $dataValidate[0]))
        {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body']               = json_encode([
            'error' => 'Invalid input'
        ]);

        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body']               = null;

        return $response;
    }

}