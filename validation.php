<?php

function ibw_validate_email_and_password($email, $password) {
	$validation_email = [
		'status' => false,
		'message' => null
	];
	$validation_password = [
		'status' => false,
		'message' => null
	];
	if ($email != "") {
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$validation_email['status'] = true;
		} else {
			$validation_email['message'] = "NOT a valid email address.";
		}
	} else {
		$validation_email['message'] = 'Please enter your email address.';
	}
	
	if ($password != "") {
		$password = filter_var($password, FILTER_SANITIZE_STRING);
		if ($password == "") {
			$validation_password['message'] = 'Please enter a valid password.';
		} else {
			$validation_password['status'] = true;
		}
	} else {
		$validation_password['message'] = 'Please enter your password.';
	}
	
	if($validation_email['status'] == true && $validation_password['status'] == true){
		$result = [
			'status' => true,
			'message' => 'Success Data',
			'email' => $email,
			'password' => $password
		];
	} else {
		if($validation_email['status'] == false){
			$message = $validation_email['message'];
		} else {
			$message = $validation_password['message'];
		}
		$result = [
			'status' => false,
			'message' => $message,
		];
	}
	
	return $result;
}

function ibw_validate_widget_id_and_status($widget_id, $status) {
	$validation_widget_id = [
		'status' => false,
		'message' => null
	];
	$validation_status = [
		'status' => false,
		'message' => null
	];

	if ($widget_id && $widget_id != "") {
		$widget_id = filter_var($widget_id, FILTER_SANITIZE_NUMBER_INT);
		if (filter_var($widget_id, FILTER_VALIDATE_INT)) {
			$validation_widget_id['status'] = true;
		} else {
			$validation_widget_id['message'] = "NOT a valid widget id.";
		}
	} else {
		$validation_widget_id['message'] = 'Widget Id not found.';
	}
	
	if ($status && $status != "") {
		$status = filter_var($status, FILTER_SANITIZE_STRING);
		if ($status == "") {
			$validation_status['message'] = 'NOT a valid widget status.';
		} else {
			if($status == 'TRUE' || $status == 'FALSE'){
				$validation_status['status'] = true;
			} else {
				$validation_status['message'] = 'NOT a valid widget status.';
			}
		}
	} else {
		$validation_status['message'] = 'NOT a valid widget status.';
	}

	if($validation_widget_id['status'] == true && $validation_status['status'] == true){
		$result = [
			'status' => true,
			'message' => 'Success Data',
			'widtget_id' => $widget_id,
			'widtget_status' => $status
		];
	} else {
		if($validation_widget_id['status'] == false){
			$message = $validation_widget_id['message'];
		} else {
			$message = $validation_status['message'];
		}
		$result = [
			'status' => false,
			'message' => $message,
		];
	}
	
	return $result;
}