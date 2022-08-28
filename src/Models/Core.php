<?php

namespace App\Models;
use App\Database;

class Core{
    public function __construct()
    {
        return $this;
    }

    public function liste($start, ?string $s = null)
    {
        $db = new Database();
        $limit = 300;

        if (isset($s)) {
            $nb_ligne = $db->queryExec("SELECT pdo.id_pdo, localite.id_loc, localite.district, localite.commune, localite.fokontany, localite.localite, localite.nb_menage_localite, localite.nb_population_localite, systeme_eau.type_infra, systeme_eau.fonctionnalite, pdo.type_pdo, pdo.localite_reservoir, pdo.etat_ouvrage, pdo.nb_menage_beneficiaire, pdo.nb_population_beneficiaire, pdo.mode_gestion, pdo.nom_gestionnaire, pdo.tarification, DATE_FORMAT(localite.date_reception, '%d/%m/%Y') AS date_reception
            FROM localite INNER JOIN systeme_eau INNER JOIN pdo
            ON localite.id_loc = systeme_eau.id_loc AND localite.id_loc = pdo.id_loc WHERE localite.id_loc LIKE '%{$s}%' OR localite.localite LIKE '%{$s}%' OR systeme_eau.type_infra LIKE '%{$s}%' OR systeme_eau.fonctionnalite LIKE '%{$s}%' OR pdo.type_pdo LIKE '%{$s}%' OR pdo.localite_reservoir LIKE '%{$s}%' OR pdo.nb_menage_beneficiaire LIKE '%{$s}%' OR pdo.nb_population_beneficiaire LIKE '%{$s}%' OR localite.date_reception LIKE '%{$s}%'");
            $n = $nb_ligne->fetchAll();
            $all = count($n);
            $nb = ceil($all / $limit);
            $query = $db->queryExec("SELECT systeme_eau.id_infra, pdo.id_pdo, localite.id_loc, localite.district, localite.commune, localite.fokontany, localite.localite, localite.nb_menage_localite, localite.nb_population_localite, systeme_eau.type_infra, systeme_eau.fonctionnalite, pdo.type_pdo, pdo.localite_reservoir, pdo.etat_ouvrage, pdo.nb_menage_beneficiaire, pdo.nb_population_beneficiaire, pdo.mode_gestion, pdo.nom_gestionnaire, pdo.tarification, DATE_FORMAT(localite.date_reception, '%d/%m/%Y') AS format_date_reception
            FROM localite INNER JOIN systeme_eau INNER JOIN pdo
            ON localite.id_loc = systeme_eau.id_loc AND localite.id_loc = pdo.id_loc WHERE localite.id_loc LIKE '%{$s}%' OR localite.localite LIKE '%{$s}%' OR systeme_eau.type_infra LIKE '%{$s}%' OR systeme_eau.fonctionnalite LIKE '%{$s}%' OR pdo.type_pdo LIKE '%{$s}%' OR pdo.localite_reservoir LIKE '%{$s}%' OR pdo.nb_menage_beneficiaire LIKE '%{$s}%' OR pdo.nb_population_beneficiaire LIKE '%{$s}%' OR localite.date_reception LIKE '%{$s}%' LIMIT {$start}, {$limit}");
            $data = $query->fetchAll();
            $end = count($data);
        } else {

            $nb_ligne = $db->queryExec("SELECT pdo.id_pdo, localite.id_loc, localite.district, localite.commune, localite.fokontany, localite.localite, localite.nb_menage_localite, localite.nb_population_localite, systeme_eau.type_infra, systeme_eau.fonctionnalite, pdo.type_pdo, pdo.localite_reservoir, pdo.etat_ouvrage, pdo.nb_menage_beneficiaire, pdo.nb_population_beneficiaire, pdo.mode_gestion, pdo.nom_gestionnaire, pdo.tarification, DATE_FORMAT(localite.date_reception, '%d/%m/%Y') AS date_reception
            FROM localite INNER JOIN systeme_eau INNER JOIN pdo
            ON localite.id_loc = systeme_eau.id_loc AND localite.id_loc = pdo.id_loc");

            $n = $nb_ligne->fetchAll();
            $all = count($n);
            $nb = ceil($all / $limit);

            $query = $db->queryExec("SELECT pdo.id_pdo, localite.id_loc, localite.id_loc, systeme_eau.id_infra, localite.district, localite.commune, localite.fokontany, localite.localite, localite.nb_menage_localite, localite.nb_population_localite, systeme_eau.type_infra, systeme_eau.fonctionnalite, pdo.type_pdo, pdo.localite_reservoir, pdo.etat_ouvrage, pdo.nb_menage_beneficiaire, pdo.nb_population_beneficiaire, pdo.mode_gestion, pdo.nom_gestionnaire, pdo.tarification, DATE_FORMAT(localite.date_reception, '%d/%m/%Y') AS format_date_reception
            FROM localite INNER JOIN systeme_eau INNER JOIN pdo
            ON localite.id_loc = systeme_eau.id_loc AND localite.id_loc = pdo.id_loc ORDER BY date_reception DESC LIMIT {$start}, {$limit}");

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

    public function distinct(string $nomTab, string $nomCol)
    {
        $db = new Database();
        $db = $db->getConnection();
        $sql = "SELECT DISTINCT({$nomCol}) FROM {$nomTab} ORDER BY {$nomCol}";
        $data = $db->query($sql);
        return $data->fetchAll();
    }

    public function chart()
    {
        $db = new Database();
        $nom_district = [];
        $taux_district = [];

        $nombre_infra = $db->queryExec('SELECT COUNT(localite) as nombre_infra FROM systeme_eau');
        $infra_fonctionnel = $db->queryExec('SELECT count(fonctionnalite) as fonctionnel FROM systeme_eau WHERE fonctionnalite = "FONCTIONNEL"');
        $infra_non_fonctionnel = $db->queryExec('SELECT count(fonctionnalite) as non_fonctionnel FROM systeme_eau WHERE fonctionnalite = "NON FONCTIONNEL"');
        $nombre_pdo = $db->queryExec('SELECT count(localite) as nombre_pdo FROM pdo');
        $pdo_fonctionnel = $db->queryExec('SELECT count(etat_ouvrage) as fonctionnel FROM pdo WHERE etat_ouvrage = "FONCTIONNEL"');
        $pdo_non_fonctionnel = $db->queryExec('SELECT count(etat_ouvrage) as non_fonctionnel FROM pdo WHERE etat_ouvrage = "NON FONCTIONNEL"');
        $taux_dessert_par_districts = $db->queryExec('SELECT year(date_reception) AS year_date_reception, localite.district, SUM(pdo.nb_population_beneficiaire)*100/SUM(localite.nb_population_localite) as taux_dessert_par_district FROM localite INNER JOIN pdo ON localite.id_loc = pdo.id_loc GROUP BY localite.district, year_date_reception');
        $taux_dessert_par_districts = $taux_dessert_par_districts->fetchAll();

        foreach($taux_dessert_par_districts as $taux_dessert_par_district){
            array_push($nom_district,  "'{$taux_dessert_par_district['district']} {$taux_dessert_par_district['year_date_reception']}'");
            array_push($taux_district, '\''.$taux_dessert_par_district['taux_dessert_par_district'].'\'');
        }

        $nom_district = implode(',', $nom_district);
        $taux_district = implode(',', $taux_district);

        return [
            'nombre_infra' => $nombre_infra->fetch(),
            'infra_fonctionnel' => $infra_fonctionnel->fetch(),
            'infra_non_fonctionnel' => $infra_non_fonctionnel->fetch(),
            'nombre_pdo' => $nombre_pdo->fetch(),
            'pdo_fonctionnel' => $pdo_fonctionnel->fetch(),
            'pdo_non_fonctionnel' => $pdo_non_fonctionnel->fetch(),
            'taux_dessert_par_districts' => $taux_dessert_par_districts,
            'nom_district' => $nom_district,
            'taux_district' => $taux_district
        ];
    }

    public function details($id_loc, $id_pdo, $id_infra)
    {
        $db = new Database;
        $loc = $db->queryExec("SELECT *, DATE_FORMAT(date_reception, '%d %M %Y') AS date_reception FROM localite wHERE id_loc = :id_loc", [':id_loc' => $id_loc]);
        $loc = $loc->fetch();
        $pdo = $db->queryExec("SELECT * FROM pdo wHERE id_pdo = :id_pdo", [':id_pdo' => $id_pdo]);
        $pdo = $pdo->fetch();
        $sys = $db->queryExec("SELECT * FROM systeme_eau wHERE id_infra = :id_infra", [':id_infra' => $id_infra]);
        $sys = $sys->fetch();
        return [
            'loc' => $loc,
            'pdo' => $pdo,
            'sys' => $sys
        ];
    }

    public function year()
    {
        $db = new Database();
        $year = $db->queryExec("SELECT DISTINCT(year(date_reception)) AS year_date_reception FROM localite ORDER BY year_date_reception");
        return $year->fetchAll();
    }

    public function taux($year)
    {
        $db = new Database();
        $nom_district = [];
        $taux_district = [];

        $taux_dessert = $db->queryExec("SELECT SUM(pdo.nb_population_beneficiaire)*100/SUM(localite.nb_population_localite) as taux_dessert FROM localite INNER JOIN pdo ON localite.id_loc = pdo.id_loc WHERE year(date_reception) = :year", [':year' => $year]);
        $taux_dessert = $taux_dessert->fetch();
        $taux_dessert_par_districts = $db->queryExec("SELECT year(date_reception) AS year_date_reception, localite.district, SUM(pdo.nb_population_beneficiaire)*100/SUM(localite.nb_population_localite) as taux_dessert_par_district FROM localite INNER JOIN pdo ON localite.id_loc = pdo.id_loc WHERE year(date_reception) = :year GROUP BY localite.district, year_date_reception", [':year' => $year]);
        $taux_dessert_par_districts = $taux_dessert_par_districts->fetchAll();

        foreach($taux_dessert_par_districts as $taux_dessert_par_district){
            array_push($nom_district,  "'{$taux_dessert_par_district['district']}'");
            array_push($taux_district, '\''.$taux_dessert_par_district['taux_dessert_par_district'].'\'');
        }

        $nom_district = implode(',', $nom_district);
        $taux_district = implode(',', $taux_district);
        return [
            'taux_dessert' => $taux_dessert,
            'nom_district' => $nom_district,
            'taux_district' => $taux_district,
            'year' => $year
        ];
    }

}
?>