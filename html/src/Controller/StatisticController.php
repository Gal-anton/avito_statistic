<?php

namespace Src\Controller;

use Src\System\DatabaseConnector;
use Src\TableGateways\StatisticGateway;

class StatisticController
{
    /**
     * @var string The client's request
     */
    private string $requestMethod;

    /**
     * @var StatisticGateway Class to work with Database
     */
    private StatisticGateway $statisticGateway;

    /**
     * StatisticController constructor.
     *
     * @param string $requestMethod Client's request
     */
    public function __construct(string $requestMethod)
    {
        $this->requestMethod = $requestMethod;

        $this->statisticGateway = new StatisticGateway(DatabaseConnector::getConnection());
    }

    /**
     * Definition of the action in order to client's request type
     */
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
     * Method GET. Get all statistic from start date to end date that
     * initialized ny client
     *
     * @return array Status code and statistic aggregated by date
     */
    private function getStatistics()
    {
        $input = (array)json_decode(file_get_contents('php://input'), true);

        $order = $input["order"] ?? null;

        if (!array_key_exists("from", $input) ||
            !array_key_exists("to", $input) ||
            !$this->validateDate($input["from"]) ||
            !$this->validateDate($input["to"]) ||
            !$this->validateOrder($order)) {
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

    /**
     * Method POST. Save statistic into database
     *
     * @return array Status code with ID of inserted row
     */
    private function createStatisticsFromRequest()
    {
        $input = (array)json_decode(file_get_contents('php://input'), true);
        if (!$this->validateStatistic($input)) {
            return $this->unprocessableEntityResponse();
        }

        $id                             = $this->statisticGateway->insert($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body']               = json_encode(["id" => $id]);

        return $response;
    }

    /**
     * Delet all saved statistic
     *
     * @return array Status code and count of deleted items
     */
    private function deleteStatistics()
    {
        $rowCount = $this->statisticGateway->delete();

        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body']               = json_encode(["count" => $rowCount]);

        return $response;
    }

    /**
     * Method to validate client data about statistic item
     *
     * @param array $input Statistic data to validate
     * @return bool        The result of validation
     */
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

        if (isset($input['cost']) && !is_numeric($input['cost'])) {
            return false;
        }

        return true;
    }

    /**
     * Validate order type
     *
     * @param string|null $order Column to order by to validate
     * @return bool The validation result
     */
    private function validateOrder(?string $order) {
        $validOrders = ["date", "views", "clicks", "cost", "cpc", "cpm"];

        return is_null($order) || in_array($order, $validOrders);
    }

    /**
     * Data must be formatted "YYYY-MM-DD"
     * @param string $date Date to validate
     * @return bool The validation result
     */
    private function validateDate(string $date)
    {
        $dataValidate = explode('-', $date);

        return count($dataValidate) === 3 &&
            checkdate($dataValidate[1], $dataValidate[2], $dataValidate[0]);
    }

    /**
     * Invalid data message to client
     *
     * @return array Status code and error message
     */
    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body']               = json_encode([
            'error' => 'Invalid input'
        ]);

        return $response;
    }

    /**
     * Send error message if the method does not exist
     *
     * @return array Status code (error)
     */
    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body']               = null;

        return $response;
    }

}