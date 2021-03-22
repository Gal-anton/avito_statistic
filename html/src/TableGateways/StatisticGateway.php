<?php
namespace Src\TableGateways;

class StatisticGateway {

    /**
     * Database connection
     * @var ?\PDO
     */
    private $db = null;

    /**
     * StatisticGateway constructor
     * @param $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Get statistic grouped by date within the client date period
     *
     * @param string $fromDate Date the statistic needed from
     * @param string $toDate   Date the statistic needed until
     * @param string $order    The order the statistic is presented
     * @return array           The statistic
     */
    public function findAll($fromDate, $toDate, $order = "date")
    {
        $statement = "
            SELECT date
                    ,SUM(views)  AS views
                    ,SUM(clicks) AS clicks
                    ,SUM(cost)   AS cost
                    ,ROUND(SUM(cost) / SUM(clicks), 2)       AS cpc
                    ,ROUND(SUM(cost) / SUM(views) * 1000, 2) AS cpm
                FROM Statistic
                WHERE date >= :fromDate AND date <= :toDate
                GROUP BY date
                ORDER BY " . $order;

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(
                array(
                    ":fromDate" => $fromDate,
                    ":toDate" => $toDate
                ));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Save statistic to database
     *
     * @param array $input The statistic to save
     * @return string      ID the last inserted row
     */
    public function insert(Array $input)
    {
        $statement = "
            INSERT INTO Statistic
                (date, views, clicks, cost)
            VALUES
                (:date, :views, :clicks, :cost);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'date' => $input['date'],
                'views'  => $input['views'],
                'clicks' => $input['clicks'] ?? null,
                'cost' => round($input['cost'], 2) ?? null,
            ));
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Delete all saved statistic
     *
     * @return int Count of deleted items
     */
    public function delete()
    {
        $statement = "
            DELETE
            FROM Statistic
            WHERE true
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}