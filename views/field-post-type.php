<ul>
<?php foreach ( $post_types as $type ): ?>
    <li>
        <input id="post-type-<?php echo $type['slug']?>" type="checkbox" name="<?php echo $settings_slug?>[]" value="<?php echo $type['slug']?>"<?php echo $type['enabled'] ? ' checked' : ''?>>
        <label for="post-type-<?php echo $type['slug']?>" ><?php echo $type['name']?></label>
    </li>
<?php endforeach;?>
</ul>
