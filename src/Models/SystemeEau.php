<?php

namespace App\Models;
use App\Database;
use Valitron\Validator;

class SystemeEau{

    public function __construct()
    {
        return $this;
    }

    public function liste($start, ?string $s = null)
    {
        $db = new Database;
        $limit = 100;

        if (isset($s)) {
            $nb_ligne = $db->queryExec("SELECT * FROM systeme_eau WHERE id_loc LIKE '%{$s}%' OR type_infra LIKE '%{$s}%' OR fonctionnalite LIKE '%{$s}%'");
            $n = $nb_ligne->fetchAll();
            $all = count($n);
            $nb = ceil($all / $limit);
            $query = $db->queryExec("SELECT * FROM systeme_eau WHERE id_loc LIKE '%{$s}%' OR type_infra LIKE '%{$s}%' OR fonctionnalite LIKE '%{$s}%' LIMIT {$start}, {$limit}");
            $data = $query->fetchAll();
            $end = count($data);
        } else {

            $nb_ligne = $db->queryExec("SELECT * FROM systeme_eau");

            $n = $nb_ligne->fetchAll();
            $all = count($n);
            $nb = ceil($all / $limit);

            $query = $db->queryExec("SELECT * FROM systeme_eau ORDER BY id_infra LIMIT {$start}, {$limit}");

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

        $newSys = $db->prepare('INSERT INTO systeme_eau(id_loc, type_infra, fonctionnalite)VALUES
        (:id_loc, :type_infra, :fonctionnalite) ');

        $v = new Validator($new);
        $v->rules([
            'required' => [
                ['type_infra'],
                ['fonctionnalite']
            ],
            'lengthBetween' => [
                ['fonctionnalite',5,20]
            ]
        ]);

        if ($v->validate()) {

            $dataSys = [
                ':id_loc' => $new['id_loc'],
                ':type_infra' => $new['type_infra'],
                ':fonctionnalite' => $new['fonctionnalite']
            ];

            $newSys->execute($dataSys);

            return ['error' => null];
        } else {
            return ['error' => $v->errors()];
        }

    }

    public function delete($id_infra)
    {
        $db = new Database;
        $db->queryExec("DELETE FROM systeme_eau wHERE id_infra = :id_infra", [':id_infra' => $id_infra]);
    }

    public function value($id_infra)
    {
        $db = new Database;
        $systeme_eau = $db->queryExec("SELECT * FROM systeme_eau wHERE id_infra = :id_infra", [':id_infra' => $id_infra]);
        $sys = $systeme_eau->fetch();

        return ['systeme_eau' => $sys];
    }

    public function update($id_infra, array $new = [])
    {
        $db = new Database();
        $db = $db->getConnection();

        $newSys = $db->prepare("UPDATE systeme_eau SET id_loc = :id_loc, type_infra = :type_infra, fonctionnalite = :fonctionnalite WHERE id_infra = {$id_infra}");

        $v = new Validator($new);
        $v->rules([
            'required' => [
                ['type_infra'],
                ['fonctionnalite']
            ],
            'lengthBetween' => [
                ['fonctionnalite',5,20]
            ]
        ]);

        if ($v->validate()) {

            $dataSys = [
                ':id_loc' => $new['id_loc'],
                ':type_infra' => $new['type_infra'],
                ':fonctionnalite' => $new['fonctionnalite']
            ];

            $newSys->execute($dataSys);

            return ['error' => null];
        } else {
            return ['error' => $v->errors()];
        }
    }

}

?>