<div class="wrap">
    <h1><?php echo esc_html__('Search everything configurations (version' . SE_DF_VERSION . ')', DOMAIN) ?></h1>
    <p><?php _e('This is desciptions for plugin.', DOMAIN); ?></p>
    <h3><?php _e('Configurations:', DOMAIN); ?></h3>
    <form method="post" action="" class="ui form">
        <table class="form-table">
            <tr>
                <th><span class="sub-title"><?php _e('Enable tool search everything on screen:') ?></span></th>
                <td>
                    <div class="ui toggle checkbox">
                        <input type="checkbox" name="enable_featured_search_everything">
                        <label></label>
                    </div>
                </td>
            </tr>
            <tr scope="row">
                <th><span class="sub-title"><?php _e('Search everything items:') ?></span></th>
                <td>
                    <div class="grouped fields">
                        <?php
                        if ($taxonomies) {
                            foreach ($taxonomies as $taxonomy) { ?>
                                <div class="field">
                                    <div class="ui checkbox">
                                        <input type="checkbox" name="dra_se_<?= $taxonomy ?>">
                                        <label><?php echo esc_html__(ucwords(str_replace('_', ' ', $taxonomy)), DOMAIN); ?></label>
                                    </div>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </table>
        <div class="submit">
            <input type="submit" name="submit" id="submit" value="Save Changes" class="ui primary button">
        </div>
    </form>
</div>