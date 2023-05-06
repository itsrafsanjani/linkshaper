<?php

$errors = [];

// function to shorten URL and save to database
function shorten_url() {
	global $wpdb, $errors;
	$table_name = $wpdb->prefix . 'linkshaper';
	$long_url   = $_POST['long_url'];
	// check if it's a valid URL
	if ( ! filter_var( $long_url, FILTER_VALIDATE_URL ) ) {
		$errors[]           = 'Please enter a valid URL';
		$_SESSION['errors'] = $errors;

		return;
	}

	// check if this URL has already been shortened
	$short_code = $wpdb->get_var( "SELECT short_code FROM $table_name WHERE long_url = '$long_url'" );
	if ( $short_code ) {
		$errors[]           = 'This URL has already been shortened';
		$_SESSION['errors'] = $errors;

		return;
	}
	$short_code = substr( md5( $long_url ), 0, 8 ); // generate short URL using MD5 hash
	try {
		$wpdb->insert( $table_name, array( 'long_url' => $long_url, 'short_code' => $short_code ) );
		echo '<div class="">Short URL: ' . home_url() . '/' . $short_code . '</div>'; // display short URL to user
	} catch ( Exception $e ) {
		echo 'Error: ' . $e->getMessage();
	}
}

// function to delete URL from database
function delete_url() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'linkshaper';
	$id         = $_POST['id'];
	try {
		$wpdb->delete( $table_name, array( 'id' => $id ) );
	} catch ( Exception $e ) {
		echo 'Error: ' . $e->getMessage();
	}
}

// process form submission
if ( isset( $_POST['submit'] ) ) {
	shorten_url();
}

if ( isset( $_POST['delete'] ) ) {
	delete_url();
}

// get URLs from database
global $wpdb;
$table_name = $wpdb->prefix . 'linkshaper';
$rows       = $wpdb->get_results( "SELECT * FROM $table_name" );

$errors = $_SESSION['errors'] ?? [];

unset( $_SESSION['errors'] );
?>
<div class="linkshaper">
	<h1>LinkShaper</h1>

	<?php
	if ( ! empty( $errors ) ) {
		foreach ( $errors as $error ) {
			echo <<<HTML
<div class="notice notice-error is-dismissible"><p>$error</p></div>
HTML;
		}
	}
	?>
	<form method="post">
		<label for="long_url">Long URL:</label>
		<input type="text" name="long_url" id="long_url" required>
		<button type="submit" name="submit" class="button button-primary">Shorten URL</button>
	</form>

	<br>

	<!-- show table using foreach $rows -->
	<table class="wp-list-table widefat fixed striped table-view-list pages">
		<thead>
		<tr>
			<th>Long URL</th>
			<th>Short Code</th>
			<th>Action</th>
		</tr>
		</thead>
		<tbody>
		<?php if ( count( $rows ) == 0 ) : ?>
			<tr>
				<td colspan="3">No URLs found</td>
			</tr>
		<?php else: ?>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><?php echo $row->long_url; ?></td>
					<td><?php echo $row->short_code; ?>
						<a href="<?php echo home_url() . '/' . $row->short_code; ?>" target="_blank">Visit</a>
					</td>
					<td>
						<form method="post">
							<input type="hidden" name="id" value="<?php echo $row->id; ?>">
							<button type="submit" name="delete" class="button button-primary">Delete</button>
						</form>
						<?php if ( isset( $_POST['delete'] ) ) {
							delete_url();
						} ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
	</table>
</div>
