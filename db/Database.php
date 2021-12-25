<?php

namespace app\core\db;

use app\core\Application;

class Database
{
    public \PDO $pdo;

    public function __construct(array $config)
    {
        $dsn = $config['dsn'] ?? '';
        $user = $config['user'] ?? '';
        $password = $config['password'] ?? '';
        $this -> pdo = new \PDO($dsn, $user, $password); //Domain service name, user i password
        $this -> pdo -> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //Izbacuje errro prilikom konekcije sa bazom

    }

    public function applyMigrations()
    {
        $this -> createMigrationsTable();
        $appliedMigrations = $this -> getAppliedMigrations();

        $newMigrations = []; //Ovo smo ubacili da bismo pratili da su emigracije nove ili vec uradjene
        $files = scandir(Application::$ROOT_DIR.'/migrations');
        $toApplyMigrations = array_diff($files, $appliedMigrations); //Gledamo pomocu diff funkcije koje migracije su uradjene a koje nisu
        foreach ($toApplyMigrations as $migration)
        {
            if ($migration === '.' || $migration === '..') {
                continue; //Izadji iz ove trenutne instance foreach petlje i predji na sledecu || MOZDA NISAM SIGURAN
            }

            require_once Application::$ROOT_DIR.'/migrations/'.$migration; //Nabacljamo tu migraciju

            $className = pathinfo($migration, PATHINFO_FILENAME);
            $instance = new $className();
            $this ->log("Applying migration $migration".PHP_EOL); //concat .php na kraju teksta
            $instance -> up();
            $this ->log("Applied migration $migration".PHP_EOL);
            $newMigrations[] = $migration;
        }

        if (!empty($newMigrations)) { //Ako array nije prazan uradi migracije ako jest kazi da su migracije vec sve uradjene
            $this -> saveMigrations($newMigrations);
        } else {
            $this -> log("All migrations are applied");
        }
    }

    public function createMigrationsTable()
    {
        $this -> pdo -> exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migrations VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
            ) ENGINE=INNODB;
        ");
    }

    public function getAppliedMigrations(): bool|array
    {
        $statement = $this -> pdo -> prepare("
            SELECT migration FROM migrations
        ");
        $statement -> execute();

        return $statement -> fetchAll(\PDO::FETCH_COLUMN); //Fetsch sve vrednosti polja migrations i upisati ih u jedan array
    }

    public function saveMigrations(array $migrations)
    {
        $string = implode(", ", array_map(fn($m) => "('$m')", $migrations)); //Formatiranje podataka u odgovarajuci oblik

        $statement = $this -> pdo -> prepare("
            INSERT INTO migrations (migration) VALUES
                $string
        ");

        $statement -> execute();
    }

    protected function log($message) //Necemo pisati poruke preko echo nego cemo ovako
    {
        echo '['.date('Y-m-d H:i:s').'] - '.$message.PHP_EOL;
    }

    public function prepare($sql): bool|\PDOStatement //Ovo smo napisali  da bi nam kod bio malo laksi za citanje
    {
        return $this -> pdo -> prepare($sql);
    }
}

/*
 *         echo '<pre>';
        var_dump($toApplyMigrations);
        echo '</pre>';
        exit;*/