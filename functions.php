<?php
require __DIR__ . '/validation.php';
require __DIR__ . '/db.php';
require __DIR__ . '/page.php';

const URL_AUTH = 'https://dashboard.ivcbox.com/external/wp/auth';
const URL_APPS = 'https://dashboard.ivcbox.com/external/wp/apps';


function ibw_widget_script() {
	$hash = ibw_get_widget_hash();
	if($hash){
		wp_enqueue_script( 'script', 'https://vc.ivcbox.com/v2/js/index.js?hash=' . $hash, array ( 'jquery' ), 1.0, true);
	}	
}
add_action( 'wp_enqueue_scripts', 'ibw_widget_script' );


function ibw_init($type, $email = null, $password = null){
	switch ($type) {
		case 'WITH_AUTH':
			$token = ibw_get_token($email, $password);
			if($token['status'] == true){
				ibw_save_token($token['token']);
			}
		break;
		case 'BY_TOKEN':
			$token = ibw_get_token_from_db();
		break;
	}
	if($token['status'] == true){
		$apps = ibw_get_apps($token['token']);
		if($apps['status'] == true){
			ibw_update_apps($apps['models']);
			$result = [
				'status' => true,
				'message' => 'The list of widgets has been updated successfully.'
			];
		} else {
			$result = [
				'status' => false,
				'message' => $apps['message']
			];
		}
	} else {
		$result = [
			'status' => false,
			'message' => $token['message']
		];
	}
	return $result;
}

function ibw_get_token($email = null, $password = null){
	$result = null;
	$request = [
		'email' => $email,
		'password' => $password
	];
	$curl = wp_remote_post( URL_AUTH, [
		'timeout'     => 100,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => [
			'Content-Type' => 'application/json'
		],
		'body' => json_encode($request)
	]);
	$result = json_decode($curl['body'], true);
	if($result){
		switch ($result['status']) {
			case true:
				$result['message'] = 'Token successfully received.';
			break;
			case false:
				$result['message'] = 'Invalid email or password.';
			break;
		}
	} else {
		$result = [
			'status' => false,
			'message' => 'System error. Please contact IvcBox support.'
		];
	}
	return $result;
}

function ibw_get_apps($token){
	$result = null;
	$curl = wp_remote_post( URL_APPS, [
		'timeout'     => 100,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => [
			'Content-Type' => 'application/json',
			'token' => $token
		]
	]);
	$result = json_decode($curl['body'], true);
	if($result){
		switch ($result['status']) {
			case true:
				$result['message'] = 'The list of widgets has been updated successfully.';
			break;
			case false:
				$result['message'] = 'The token has expired. You need to enter an email and password to update the list of widgets.';
			break;
		}
	} else {
		$result = [
			'status' => false,
			'message' => 'System error. Please contact IvcBox support.'
		];
	}
	return $result;
}


