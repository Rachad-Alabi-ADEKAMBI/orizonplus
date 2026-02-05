<?php

function db()
{
    static $pdo;
    if (!$pdo) {
        try {
            $pdo = new PDO(
                "mysql:host=localhost;dbname=orizonplus;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            jsonError("Connexion DB échouée : " . $e->getMessage());
        }
    }
    return $pdo;
}
