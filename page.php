<?php

add_action( 'admin_menu', 'ibw_register_page' );

function ibw_register_page(){
	add_menu_page( 'IvcBox Widgets', 'IvcBox Widgets', 'manage_options', 'ibw_view_index', 'ibw_view_index', plugins_url( 'ivc-box-widget/images/icon.png' ), 3 );
}

function ibw_view_index() {
	wp_enqueue_style('mca-table-style', plugins_url('/assets/css/ibw-table-style.css', __FILE__));
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient pilchards to access this page.')    );
	}
	echo "<h1>IvcBox Widgets</h1><hr>";
	
	if (isset($_POST['update_widgets']) && check_admin_referer('update_button_clicked')) {
		$validation = ibw_validate_email_and_password($_POST['update_email'], $_POST['update_password']);
		if($validation['status'] == true){
			$result = ibw_init('WITH_AUTH', $validation['email'], $validation['password']);
		} else {
			$result = $validation;
		}
	} else if (isset($_POST['ibw_select_widget']) && check_admin_referer('ibe_select_button_clicked')) {
		$validation = ibw_validate_widget_id_and_status($_POST['ibw_select_widget_id'], $_POST['ibw_select_widget_status']);
		if($validation['status'] == true){
			$result = ibw_update_widget_is_selected($validation['widtget_id'], $validation['widtget_status']);
		} else {
			$result = $validation;
		}
	} else {
		$result = ibw_init('BY_TOKEN');
	}
	
	echo '<div id="message" class="updated fade ' . (($result['status'] == true) ? 'success' : 'error') . '"><p>' . esc_html($result['message']) . '</p></div>';
	
	echo '<form class="ibw-update-form" action="options-general.php?page=ibw_view_index" method="post">';
		wp_nonce_field('update_button_clicked');
		echo '<input type="hidden" value="true" name="update_widgets" />';
		echo '<div class="form-input">
			<input type="text" placeholder="Email" name="update_email" required />
		</div>';
		echo '<div class="form-input">
			<input type="text" placeholder="Password" name="update_password" required />
		</div>';
		submit_button('Update Widgets');
	echo '</form>';
	
	$items_per_page = 5;
	$page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
	$results = ibw_get_widgets_list($items_per_page, $page);
	if($results['widgets']){
		echo "<h3>List of Widgets</h3>";
		echo "<table class='orders_table'>";
			echo "<tr>";
				echo "<th>ID</th>";
				echo "<th>WebSite</th>";
				echo "<th>Widget Key</th>";
				echo "<th>Widget status in IvcBox</th>";
				echo "<th>Widget Activation</th>";
			echo "</tr>";
			foreach($results['widgets'] as $widget){
				echo "<tr>";
					echo "<td>" . esc_html($widget->id) . "</td>";
					echo "<td>" . esc_html($widget->site) . "</td>";
					echo "<td>" . esc_html($widget->hash) . "</td>";
					echo "<td>" . ($widget->status == 1 ? 'Active' : 'Disabled') . "</td>";
					echo '<td><form id="ibw-select-form-' . esc_html($widget->id) . '" сlass="ibw-select-form" action="options-general.php?page=ibw_view_index" method="post"> ' . wp_nonce_field('ibe_select_button_clicked') . '
						<input type="hidden" value="true" name="ibw_select_widget" />
						<input type="hidden" value="' . esc_html($widget->isSelected) . '" name="ibw_select_widget_status" />
						<input type="hidden" value="' . esc_html($widget->id) . '" name="ibw_select_widget_id" />
						<button class="' . ($widget->isSelected == 'TRUE' ? 'ibw-button-error' : 'ibw-button-success') . '" type="submit" form="ibw-select-form-' . esc_html($widget->id) . '">' . ($widget->isSelected == 'TRUE' ? 'Deactivate' : 'Activate') . '</button>
					</form></td>';
				echo "</tr>";
			}
		echo "</table>";
		
		echo paginate_links( array(
			'base' => add_query_arg( 'cpage', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => ceil($results['total'] / $items_per_page),
			'current' => $page,
			'type'     => 'list'
		));
	}
}