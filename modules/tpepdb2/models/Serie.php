<?php

require_once MODULES . DS . 'tpepdb2' . DS . 'vendor' . DS . 'tpepdb' . DS . 'TpePdbModel.php';

class Tpepdb2_Serie extends TpePdbModel
{
    public $has_many = array(
        'Compound',
        'SerieDocument',
        'SerieValues',
    );

    public $belongs_to = array(
        'Brand',
    );

    public function searchViaProperties($market, $application, $advantage, $language, $language_id, $region_id = null, $with_template = true, $search_in_values = false, $found_series = array())
    {
        $conditions = array();

        $region = false;
        $region_join = $region_clause = '';
        if (true === is_int($region_id) && $region_id > 0) {
            $region = true;
            $conditions[] = 'SerieRegion.region_id = ' . $region_id;
            $region_join = 'INNER JOIN `ncw_tpepdb2_serie_region` AS sr ON s.id=sr.serie_id';
            $region_clause = ' && sr.region_id=:region_id';
        }

        if (true === $search_in_values) {
            $condition = '(m.' . $language . ' LIKE :market || a.' . $language . ' LIKE :application || mv.' . $language . ' LIKE :advantage)';
            $market = '%' . $market . '%';
            $application = '%' . $application . '%';
            $advantage = '%' . $advantage . '%';
        } else {
            $condition = 'm.num=:market';
            if (false === empty($application)) {
                $condition .= ' && a.num=:application';
            }
            if (false === empty($advantage)) {
                $condition .= ' && mv.num=:advantage';
            }
        }

        $sql = 'SELECT DISTINCT s.id
            FROM `ncw_tpepdb2_serie` AS s
            INNER JOIN `ncw_tpepdb2_serie_values` AS sv ON sv.serie_id=s.id
            ' . $region_join . "
            INNER JOIN `ncw_tpepdb2_serie_markets` AS sm ON sm.serie_id=s.id
            INNER JOIN `ncw_tpepdb2_markets` AS m ON sm.markets_id=m.id
            INNER JOIN `ncw_tpepdb2_serie_anwendungsbereiche` AS sa ON sa.serie_id=s.id
            INNER JOIN `ncw_tpepdb2_anwendungsbereiche` AS a ON sa.anwendungsbereiche_id=a.id
            INNER JOIN `ncw_tpepdb2_serie_materialvorteile` AS smv ON smv.serie_id=s.id
            INNER JOIN `ncw_tpepdb2_materialvorteile` AS mv ON smv.materialvorteile_id=mv.id
            WHERE s.status='portfolio' " . $region_clause . ' && ' . $condition . '
            LIMIT 25';
        $sth = $this->db->prepare($sql);
        if ($region_id > 0) {
            $sth->bindValue(':region_id', $region_id);
        }
        $sth->bindValue(':market', $market);
        if (false === empty($application)) {
            $sth->bindValue(':application', $application);
        }
        if (false === empty($advantage)) {
            $sth->bindValue(':advantage', $advantage);
        }
        $sth->execute();
        $results = $sth->fetchAll();

        $serie_conditions = array();
        foreach ($results as $result) {
            if (true == in_array($result['id'], $found_series)) {
                continue;
            }
            $serie_conditions[] = 'Serie.id=' . $result['id'];
        }
        $series = array();
        if (false === empty($serie_conditions)) {
            $conditions[] = '(' . implode(' || ', $serie_conditions) . ')';
            $series = $this->_readAllOf(
                'Serie',
                $language,
                $conditions,
                $region,
                array($this->name . '.brand_id')
            );
        }

        if (true === $with_template) {
            ob_start();
            include_once ASSETS . DS . 'tpepdb2' . DS . 'templates' . DS . 'search_templates' . DS . 'serie_search.phtml';
            return ob_get_clean();
        } else {
            return $series;
        }
    }
}
?>
