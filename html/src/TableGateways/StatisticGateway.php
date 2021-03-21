<?php
namespace Src\TableGateways;

class StatisticGateway {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

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
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function delete()
    {
        $statement = "
            TRUNCATE TABLE Statistic
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