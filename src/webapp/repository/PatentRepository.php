<?php

namespace tdt4237\webapp\repository;

use PDO;
use tdt4237\webapp\models\Patent;
use tdt4237\webapp\models\PatentCollection;

class PatentRepository
{

    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function removeSpecialChars($string){
      $result = preg_replace('/[^A-Za-z0-9 !@#$%^&*().]/u','', strip_tags($string));
      return $result;
    }

    public function makePatentFromRow(array $row)
    {
        $patent = new Patent($row['patentId'], $row['company'], $row['title'], $row['description'], $row['date'], $row['file']);
        $patent->setPatentId($row['patentId']);
        $patent->setCompany($row['company']);
        $patent->setTitle($row['title']);
        $patent->setDescription($row['description']);
        $patent->setDate($row['date']);
        $patent->setFile($row['file']);

        return $patent;
    }

    public function search($query)
    {

        $formatted = "%" . $query ."%";
        $stmt = $this->pdo->prepare("SELECT * FROM patent WHERE company LIKE :query OR title LIKE :query");
        $stmt->execute(['query' => $formatted,'query' => $formatted]);
        $fetch = $stmt->fetchAll();
        if(count($fetch) == 0) {
            return false;
        }

        return new PatentCollection(
            array_map([$this, 'makePatentFromRow'], $fetch)
        );
    }


    public function find($patentId)
    {
        $sql  = "SELECT * FROM patent WHERE patentId = $patentId";
        $result = $this->pdo->query($sql);
        $row = $result->fetch();

        if($row === false) {
            return false;
        }


        return $this->makePatentFromRow($row);
    }

    public function all()
    {
        $sql   = "SELECT * FROM patent";
        $results = $this->pdo->query($sql);

        if($results === false) {
            return [];
            throw new \Exception('PDO error in patent all()');
        }

        $fetch = $results->fetchAll();
        if(count($fetch) == 0) {
            return false;
        }

        return new PatentCollection(
            array_map([$this, 'makePatentFromRow'], $fetch)
        );
    }

    public function deleteByPatentid($patentId)
    {
        return $this->pdo->exec(
            sprintf("DELETE FROM patent WHERE patentid='%s';", $patentId));
    }


    public function save(Patent $patent)
    {
        $title          = $this -> removeSpecialChars($patent->getTitle());
        $company        = $this -> removeSpecialChars($patent->getCompany());
        $description    = $this -> removeSpecialChars($patent->getDescription());
        $date           = $patent->getDate();
        $file           = $patent->getFile();

        if ($file === null){
          $file = "lslsls";
        }

        if ($patent->getPatentId() === null) {
            $stmt = $this->pdo->prepare("INSERT INTO patent (company, date, title, description, file) "
              . "VALUES (:company, :date, :title, :description, :file)");
        }
        $stmt->execute(['title' => $title, 'company' => $company, 'description' => $description, 'date' => $date, 'file' => $file]);


        return  $this->pdo->lastInsertId();
    }
}
