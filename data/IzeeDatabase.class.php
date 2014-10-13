<?php

// File required for use dbDelta function
require_once(ABSPATH.'wp-admin/includes/upgrade.php');

class IzeeDatabase {

	/**
	 * Vérifie si la/les tables existent en base
	 * 
	 * @param  array $tables Tables à vérifier
	 * @author  Kevin B. Apizee Inc
	 */
	public function tableExist( $tables ) {
		global $wpdb;

		for ($i=0;count($tables);$i++) :
			$tmp = $tables[$i];
			if ( $wpdb->get_var( "show tables like '$tmp'" ) != $tmp ) :
				return false;
			else :
				return true;
			endif;
		endfor;
	}

	/**
	 * Récupère les infos d'une ligne sur une table via une requête sql
	 * 
	 * @param  string $request Requête SQL
	 * @return array  $tab 	   Tableau de données
	 * @author  Kevin B. Apizee Inc
	 */
	public function getResultTable( $request ) {
		global $wpdb;
		$tab=array();

		$donnees = $wpdb->get_row($request, ARRAY_A);
		if ( $donnees ) :
			$tab = $donnees;
		endif;

		return $tab;
	}

	/**
	 * Récupère les infos de toutes les lignes ligne sur une table via une requête sql
	 * @param  string $request Requête SQL
	 * @param  string $action  Action à faire sur le retour de données
	 * @return array  $tab     Tableau de données
	 * @author  Kevin B. Apizee Inc
	 */
	public function getResultsTable( $request, $action = NULL ) {
		global $wpdb;
		$tab=array();
		$tab2=array();
		$i=0;

		$donnees = $wpdb->get_results($request, ARRAY_A);
		if ( $donnees ) :
			foreach ($donnees as $key => $value) :
					$tab[$i] = $value;
					$i++;
			endforeach;
		endif;

		if ( $action == "push" ):
			for($i=0;$i<count($tab);$i++) :
				$val = (string)$tab[$i]['email'];
				array_push( $tab2, $val );
			endfor;
			return $tab2;
		endif;

		return $tab;
	}

	/**
	 * Crée une nouvelle table dans la base
	 * 
	 * @param  string $table   Nom de la table
	 * @param  array  $content Champs à créer dans cette table
	 * @return bool   		   True/False
	 * @author  Kevin B. Apizee Inc
	 */
	public function createTable( $table, $content=array() ) {
		if ( !empty($content) ) :
			$request  = "CREATE TABLE " . $table . " (";
			foreach ($content as $key => $value) {
				$request .= $key." ". $value.",";
				if ($key == "UNIQUE KEY") :
					$request .= $key." ". $value;  
				endif;  
			}
			$request .= ")ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			dbDelta($request);
			return true;
		else :
			return false;
		endif;
	}

	/**
	 * Insert des données dans la table
	 * 
	 * @param  string $table   Nom de la table
	 * @param  array  $content Données à insérer
	 * @return bool            True/False
	 * @author  Kevin B. Apizee Inc
	 */
	public function insertInTable( $table, $content ) {
		global $wpdb;

		if ( !empty($content) ) :
			$request = $wpdb->insert($table, $content);
			if ( $request ) :
				return true;
			else :
				return false;
			endif;
		else:
			return false;
		endif;
	}

}

?>