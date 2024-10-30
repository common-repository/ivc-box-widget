<?php

function ibw_create_db( ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "ibw_token";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			token TEXT NOT NULL,
			UNIQUE KEY id (id)
		);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	$table_name = $wpdb->prefix . "ibw_apps";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			hash VARCHAR(255) NOT NULL,
			site VARCHAR(255),
			status VARCHAR(255),
			isSelected VARCHAR(255) NOT NULL,
			UNIQUE KEY id (id)
		);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

/*TOKEN*/
function ibw_save_token( $token ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "ibw_token";
	$wpdb->query("TRUNCATE TABLE $table_name");
	$wpdb->insert( $table_name, 
		[ 
			'token' => $token,
			'created_at' => date("Y-m-d H:i:s"),
			'updated_at' => date("Y-m-d H:i:s"),
		]
	);
	$wpdb->insert_id;
}

function ibw_get_token_from_db( ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "ibw_token";
	$token_info =  $wpdb->get_results( "SELECT token FROM $table_name" );
	if($token_info){
		$result = [
			'status' => true,
			'token' => $token_info[0]->token,
			'message' => 'Success geting token from DB'
		];
	} else {
		$result = [
			'status' => false,
			'message' => 'Please enter your login and password in order to receive an authorization token. The list of widgets is updated automatically when the page reloads if your token has not expired.'
		];
	}
	return $result;
}
/*END TOKEN*/

function ibw_get_widgets_list( $items_per_page, $page ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "ibw_apps";
	$offset = ( $page * $items_per_page ) - $items_per_page;
	$query = 'SELECT * FROM '.$table_name;
	$total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
	$total = $wpdb->get_var( $total_query );
	$results = $wpdb->get_results( $query.' ORDER BY id DESC LIMIT '. $offset.', '. $items_per_page, OBJECT );
	return [
		'widgets' => $results,
		'total' => $total
	];
}

function ibw_update_apps( $widgets = [] ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "ibw_apps";
	$db_widgets =  $wpdb->get_results( "SELECT * FROM $table_name" );
	foreach($db_widgets as $db_widget){
		$haveWidget = false;
		foreach($widgets as $key => $widget){
			if($db_widget->hash == $widget['appKey']){
				$haveWidget = true;
				$widgets[$key]['isUpdated'] = 'UPDATED';
				$rows_affected = $wpdb->update( $table_name,
					[
						'status' => $widget['status'],
						'updated_at' => date("Y-m-d H:i:s"),
						'site' => $widget['website']
					],
					['hash' => $db_widget->hash]
				);
			}
		}
		if($haveWidget == false){
			$wpdb->delete( $table_name, ['hash' => $db_widget->hash] );
		}
	}
	
	foreach($widgets as $key => $widget){
		if($widget['isUpdated'] !== 'UPDATED'){
			$wpdb->insert( $table_name, 
				[ 
					'hash' => $widget['appKey'],
					'site' => $widget['website'],
					'status' => $widget['status'],
					'isSelected' => 'FALSE',
					'created_at' => date("Y-m-d H:i:s"),
					'updated_at' => date("Y-m-d H:i:s"),
				]
			);
		}
	}
}

function ibw_update_widget_is_selected($id, $isSelected){
	global $wpdb;
	$table_name = $wpdb->prefix . "ibw_apps";
	$db_widgets =  $wpdb->get_results( "SELECT * FROM $table_name" );
	foreach($db_widgets as $db_widget){
		$wpdb->update( $table_name,
			[
				'isSelected' => 'FALSE',
			],
			['hash' => $db_widget->hash]
		);
	}

	if($isSelected == 'TRUE'){
		$newisSelected = 'FALSE';
	} else {
		$newisSelected = 'TRUE';
	}
	
	$wpdb->update( $table_name,
		[
			'isSelected' => $newisSelected,
		],
		['id' => $id]
	);
	
	$result = [
		'status' => true,
		'message' => 'Widget activated'
	];
	return $result;
}

function ibw_get_widget_hash(){
	global $wpdb;
	$table_name = $wpdb->prefix . "ibw_apps";
	$widget_info =  $wpdb->get_results( "SELECT hash FROM $table_name WHERE isSelected = 'TRUE'" );
	if($widget_info){
		$result = $widget_info[0]->hash;
	} else {
		$result = null;
	}
	return $result;
}
