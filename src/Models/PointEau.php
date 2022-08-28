<?php

namespace App\Models;
use App\Database;
use Valitron\Validator;

class PointEau {

    public function __construct()
    {
        return $this;
    }

    public function liste($start, ?string $s = null)
    {
        $db = new Database;
        $limit = 300;

        if (isset($s)) {
            $nb_ligne = $db->queryExec("SELECT * FROM pdo WHERE id_loc LIKE '%{$s}%' OR type_pdo LIKE '%{$s}%' OR localite_reservoir LIKE '%{$s}%' OR etat_ouvrage LIKE '%{$s}%' OR
            nb_menage_beneficiaire LIKE '%{$s}%' OR nb_population_beneficiaire LIKE '%{$s}%' OR mode_gestion LIKE '%{$s}%' OR
            nom_gestionnaire LIKE '%{$s}%' OR tarification LIKE '%{$s}%'");
            $n = $nb_ligne->fetchAll();
            $all = count($n);
            $nb = ceil($all / $limit);
            $query = $db->queryExec("SELECT * FROM pdo WHERE id_loc LIKE '%{$s}%' OR type_pdo LIKE '%{$s}%' OR localite_reservoir LIKE '%{$s}%' OR etat_ouvrage LIKE '%{$s}%' OR
            nb_menage_beneficiaire LIKE '%{$s}%' OR nb_population_beneficiaire LIKE '%{$s}%' OR mode_gestion LIKE '%{$s}%' OR
            nom_gestionnaire LIKE '%{$s}%' OR tarification LIKE '%{$s}%' LIMIT {$start}, {$limit}");
            $data = $query->fetchAll();
            $end = count($data);
        } else {
            $nb_ligne = $db->queryExec("SELECT * FROM pdo");

            $n = $nb_ligne->fetchAll();
            $all = count($n);
            $nb = ceil($all / $limit);

            $query = $db->queryExec("SELECT * FROM pdo ORDER BY id_pdo LIMIT {$start}, {$limit}");

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

        $newPdo = $db->prepare('INSERT INTO pdo(id_loc, type_pdo, localite_reservoir, etat_ouvrage, nb_menage_beneficiaire, nb_population_beneficiaire, mode_gestion, nom_gestionnaire, tarification)VALUES
        (:id_loc, :type_pdo, :localite_reservoir, :etat_ouvrage, :nb_menage_beneficiaire, :nb_population_beneficiaire, :mode_gestion, :nom_gestionnaire, :tarification) ');

        $v = new Validator($new);
        $v->rules([
            'required' => [
                ['type_pdo'],
                ['localite_reservoir'],
                ['etat_ouvrage'],
                ['nb_menage_beneficiaire'],
                ['nb_population_beneficiaire'],
                ['mode_gestion'],
                ['nom_gestionnaire'],
                ['tarification']
            ],
            'lengthBetween' => [
                ['localite_reservoir',5,20],
                ['etat_ouvrage',5,20],
                ['mode_gestion',5,50],
                ['nom_gestionnaire',5,50],
                ['tarification',5,50]
            ],
            'integer' => [
                ['nb_menage_beneficiaire'],
                ['nb_population_beneficiaire']
            ],
            'min' => [
                ['nb_menage_beneficiaire', 0],
                ['nb_population_beneficiaire', 0]
            ]
        ]);

        if ($v->validate()) {

            $dataPdo = [
                ':id_loc' => $new['id_loc'],
                ':type_pdo' => $new['type_pdo'],
                ':localite_reservoir' => $new['localite_reservoir'],
                ':etat_ouvrage' => $new['etat_ouvrage'],
                ':nb_menage_beneficiaire' => $new['nb_menage_beneficiaire'],
                ':nb_population_beneficiaire' => $new['nb_population_beneficiaire'],
                ':mode_gestion' => $new['mode_gestion'],
                ':nom_gestionnaire' => $new['nom_gestionnaire'],
                ':tarification' => $new['tarification']
            ];

            $newPdo->execute($dataPdo);

            return ['error' => null];
        } else {
            return ['error' => $v->errors()];
        }

    }

    public function delete($id_pdo)
    {
        $db = new Database;
        $db->queryExec("DELETE FROM pdo wHERE id_pdo = :id_pdo", [':id_pdo' => $id_pdo]);
    }

    public function value($id_pdo)
    {
        $db = new Database;
        $pdo = $db->queryExec("SELECT * FROM pdo wHERE id_pdo = :id_pdo", [':id_pdo' => $id_pdo]);
        $pdo = $pdo->fetch();

        return ['pdo' => $pdo];
    }

    public function update($id_pdo, array $new = [])
    {
        $db = new Database();
        $db = $db->getConnection();

        $newPdo = $db->prepare("UPDATE pdo SET id_loc = :id_loc, type_pdo = :type_pdo, localite_reservoir = :localite_reservoir, etat_ouvrage = :etat_ouvrage,
        nb_menage_beneficiaire = :nb_menage_beneficiaire, nb_population_beneficiaire = :nb_population_beneficiaire, mode_gestion = :mode_gestion,
        nom_gestionnaire = :nom_gestionnaire, tarification = :tarification WHERE id_pdo = {$id_pdo}");

        $v = new Validator($new);
        $v->rules([
            'required' => [
                ['type_pdo'],
                ['localite_reservoir'],
                ['etat_ouvrage'],
                ['nb_menage_beneficiaire'],
                ['nb_population_beneficiaire'],
                ['mode_gestion'],
                ['nom_gestionnaire'],
                ['tarification']
            ],
            'lengthBetween' => [
                ['localite_reservoir',5,20],
                ['etat_ouvrage',5,20],
                ['mode_gestion',5,50],
                ['nom_gestionnaire',5,50],
                ['tarification',5,50]
            ],
            'integer' => [
                ['nb_menage_beneficiaire'],
                ['nb_population_beneficiaire']
            ],
            'min' => [
                ['nb_menage_beneficiaire', 0],
                ['nb_population_beneficiaire', 0]
            ]
        ]);

        if ($v->validate()) {

            $dataPdo = [
                ':id_loc' => $new['id_loc'],
                ':type_pdo' => $new['type_pdo'],
                ':localite_reservoir' => $new['localite_reservoir'],
                ':etat_ouvrage' => $new['etat_ouvrage'],
                ':nb_menage_beneficiaire' => $new['nb_menage_beneficiaire'],
                ':nb_population_beneficiaire' => $new['nb_population_beneficiaire'],
                ':mode_gestion' => $new['mode_gestion'],
                ':nom_gestionnaire' => $new['nom_gestionnaire'],
                ':tarification' => $new['tarification']
            ];

            $newPdo->execute($dataPdo);

            return ['error' => null];
        } else {
            return ['error' => $v->errors()];
        }

    }

}