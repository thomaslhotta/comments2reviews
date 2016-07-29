<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php esc_html_e( 'Comments 2 Reviews Settings', $slug ) ?></h2>

	<form method="post" action="options.php">
		<?php
			settings_fields( $slug . '-enabled_post_types' );
			do_settings_sections( $slug . '-enabled_post_types' );
			submit_button();
		?>
	</form>
</div>
