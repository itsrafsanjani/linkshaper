<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="linkshaper" class="linkshaper">
    <h1>LinkShaper</h1>
    <form method="post" action="">
        <label for="long_url">Long URL:</label>
        <input type="text" name="long_url" id="long_url" required>
        <br><br>
        <input type="submit" name="submit" value="Shorten URL" class="button button-primary">
    </form>

    <br>
    <button @click="increment" class="button button-primary">Click me ({{ count }})</button>
	<?php
	if ( isset( $_POST['submit'] ) ) {
		// code to shorten URL and save to database
		global $wpdb;
		$table_name = $wpdb->prefix . 'linkshaper';
		$long_url   = $_POST['long_url'];
		$short_code = substr( md5( $long_url ), 0, 8 ); // generate short URL using MD5 hash
		try {
			$wpdb->insert( $table_name, array( 'long_url' => $long_url, 'short_code' => $short_code ) );
			echo 'Short URL: ' . home_url() . '/' . $short_code; // display short URL to user
		} catch ( Exception $e ) {
			echo 'Error: ' . $e->getMessage();
		}
	}
	?>

    <!-- show table using foreach $rows -->
	<?php
	global $wpdb;
	$table_name = $wpdb->prefix . 'linkshaper';
	$rows       = $wpdb->get_results( "SELECT * FROM $table_name" );
	?>

    <table class="wp-list-table widefat fixed striped table-view-list pages">
        <thead>
        <tr>
            <th>Long URL</th>
            <th>Short Code</th>
        </tr>
        </thead>
        <tbody>
		<?php
		foreach ( $rows as $row ) : ?>
            <tr>
                <td><?php
					echo $row->long_url; ?></td>
                <td><?php
					echo $row->short_code; ?>
                    <a href="<?php
					echo home_url() . '/' . $row->short_code; ?>" target="_blank">
                        Visit
                    </a>
                </td>
            </tr>
		<?php
		endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    const {createApp} = Vue

    createApp({
        data() {
            return {
                count: 0
            }
        },
        methods: {
            increment() {
                this.count++
            }
        }
    }).mount('#linkshaper')
</script>