<?php

class Database{
    private $pdo;

    public function __construct($driver = 'mysql'){
        $host = 'localhost';
        $dbName = 'library_db';
        $username = 'root';
        $password = '';

        $dsn = '';
        if($driver = 'mysql'){
            $dsn = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";
        } elseif($driver == 'pgsql') {
            $dsn = "pgsql:host=$host;dbname=$dbName";
        }else {
            throw new Exception("Unsupported database driver: $driver");
        }

        try{
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            die(json_encode([
                'status' => 'error',
                'success' => false,
                'message' => 'Veritabanı bağlantı hatası: ' . $e->getMessage()
            ]));
        }
    }

    public function getConnection(){
        return $this->pdo;
    }
}