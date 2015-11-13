<ul>
<?php foreach ( $post_types as $type ) : ?>
    <li>
        <input id="post-type-<?php echo $type['slug']?>" type="checkbox" name="<?php echo esc_attr( $settings_slug ); ?>[]"
               value="<?php echo esc_attr( $type['slug'] ); ?>"<?php echo $type['enabled'] ? ' checked' : ''?>>
        <label for="post-type-<?php echo esc_attr( $type['slug'] ); ?>" ><?php echo esc_html( $type['name'] );?></label>
    </li>
<?php endforeach;?>
</ul>
