<?php

namespace App\Models;
use App\Database;
use Valitron\Validator;

class Localite{

    public function __construct()
    {
        return $this;
    }

    public function liste($start, ?string $s = null)
    {
        $db = new Database;
        $limit = 350;

        if (isset($s)) {
            $nb_ligne = $db->queryExec("SELECT * FROM localite WHERE id_loc LIKE '%{$s}%' OR district LIKE '%{$s}%' OR commune LIKE '%{$s}%' OR fokontany LIKE '%{$s}%' OR localite LIKE '%{$s}%' OR
            nb_menage_localite LIKE '%{$s}%' OR nb_population_localite LIKE '%{$s}%' OR date_reception LIKE '%{$s}%'");
            $n = $nb_ligne->fetchAll();
            $all = count($n);
            $nb = ceil($all / $limit);
            $query = $db->queryExec("SELECT *, DATE_FORMAT(date_reception, '%d/%m/%Y') AS format_date_reception FROM localite WHERE id_loc LIKE '%{$s}%' OR district LIKE '%{$s}%' OR commune LIKE '%{$s}%' OR fokontany LIKE '%{$s}%' OR localite LIKE '%{$s}%' OR
            nb_menage_localite LIKE '%{$s}%' OR nb_population_localite LIKE '%{$s}%' OR date_reception LIKE '%{$s}%' LIMIT {$start}, {$limit}");
            $data = $query->fetchAll();
            $end = count($data);
        } else {
            $nb_ligne = $db->queryExec("SELECT * FROM localite");

            $n = $nb_ligne->fetchAll();
            $all = count($n);
            $nb = ceil($all / $limit);

            $query = $db->queryExec("SELECT *, DATE_FORMAT(date_reception, '%d/%m/%Y') AS format_date_reception FROM localite  ORDER BY date_reception DESC LIMIT {$start}, {$limit}");

            $data = $query->fetchAll();
            $end = count($data);
        }

        return [
            'data' => $data,
            'nb' => $nb,
            'all' => $all,
            'start' => $start,
            'end' => $end
        ];
    }

    public function insertion(array $new = [])
    {
        $db = new Database();
        $db = $db->getConnection();

        $newLoc = $db->prepare('INSERT INTO localite(district, commune, fokontany, localite, nb_menage_localite, nb_population_localite, date_reception)VALUES
        (:district, :commune, :fokontany, :localite, :nb_menage_localite, :nb_population_localite, :date_reception) ');

        $v = new Validator($new);
        $v->rules([
            'required' => [
                ['district'],
                ['commune'],
                ['fokontany'],
                ['localite'],
                ['nb_menage_localite'],
                ['nb_population_localite'],
                ['date_reception']
            ],
            'lengthBetween' => [
                ['district',5,20],
                ['commune',5,20],
                ['fokontany',5,20],
                ['localite',5,20]
            ],
            'integer' => [
                ['nb_menage_localite'],
                ['nb_population_localite']
            ],
            'min' => [
                ['nb_menage_localite', 0],
                ['nb_population_localite', 0],
            ],
            'date' => [
                ['date_reception']
            ]
        ]);

        if ($v->validate()) {

            $dataLoc = [
                ':district' => $new['district'],
                ':commune' => $new['commune'],
                ':fokontany' => $new['fokontany'],
                ':localite' => $new['localite'],
                ':nb_menage_localite' => $new['nb_menage_localite'],
                ':nb_population_localite' => $new['nb_population_localite'],
                ':date_reception' => $new['date_reception']
            ];

            $newLoc->execute($dataLoc);

            return ['error' => null];
        } else {
            return ['error' => $v->errors()];
        }

    }

    public function delete($id_loc)
    {
        $db = new Database;
        $db->queryExec("DELETE FROM localite wHERE id_loc = :id_loc", [':id_loc' => $id_loc]);
    }

    public function value($id_loc)
    {
        $db = new Database;
        $localite = $db->queryExec("SELECT * FROM localite wHERE id_loc = :id_loc", [':id_loc' => $id_loc]);
        $loc = $localite->fetch();

        return ['localite' => $loc];
    }

    public function update($id_loc, array $new = [])
    {
        $db = new Database();
        $db = $db->getConnection();

        $newLoc = $db->prepare("UPDATE localite SET district = :district, commune = :commune, fokontany = :fokontany, localite = :localite, 
        nb_menage_localite = :nb_menage_localite, nb_population_localite = :nb_population_localite, date_reception = :date_reception WHERE id_loc = {$id_loc}");

        $v = new Validator($new);
        $v->rules([
            'required' => [
                ['district'],
                ['commune'],
                ['fokontany'],
                ['localite'],
                ['nb_menage_localite'],
                ['nb_population_localite'],
                ['date_reception']
            ],
            'lengthBetween' => [
                ['district',5,20],
                ['commune',5,20],
                ['fokontany',5,20],
                ['localite',5,20]
            ],
            'integer' => [
                ['nb_menage_localite'],
                ['nb_population_localite'],
            ],
            'min' => [
                ['nb_menage_localite', 0],
                ['nb_population_localite', 0],
            ],
            'date' => [
                ['date_reception']
            ]
        ]);

        if ($v->validate()) {

            $dataLoc = [
                ':district' => $new['district'],
                ':commune' => $new['commune'],
                ':fokontany' => $new['fokontany'],
                ':localite' => $new['localite'],
                ':nb_menage_localite' => $new['nb_menage_localite'],
                ':nb_population_localite' => $new['nb_population_localite'],
                ':date_reception' => $new['date_reception']
            ];

            $newLoc->execute($dataLoc);

            return ['error' => null];
        } else {
            return ['error' => $v->errors()];
        }

    }

}

?>