<?php    defined('C5_EXECUTE') or die("Access Denied.");
$form = \Core::make('helper/form' );
$ui   = \Core::make('helper/concrete/ui');
$dash = \Core::make('helper/concrete/dashboard');
$action = $this->action('save_settings');
?>

<form id="niiknow_cdn-settings" method="post" action="<?php    echo $action?>">
    <div class="clearfix ccm-pane-body">
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <td style="text-align: right"><?php    echo $form->checkbox('CDN_ENABLED', 1, $CDN_ENABLED); ?></td>
                    <td scope="row"> <?php echo $form->label('CDN_ENABLED', t('Enable CDN Support'))?></td>
                </tr>
                <tr valign="top">
                    <td scope="row"><label for="CDN_OFFSITE_URL">Off-site URL</label></td>
                    <td>
                        <?php   echo $form->text('CDN_OFFSITE_URL', array('class' => '', 'size' => '64'), $CDN_OFFSITE_URL)?>
                        <br>
                        <span class="description"><?php   echo t("The new URL to be used in place of ".BASE_URL)?> for rewriting. No trailing <code>/</code> please.<br>Example: <code><?php   echo t($CDN_OFFSITE_URL)?>/packages/fundamental/themes/fundamental/css/normalize.min.css</code>.</span>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row"><label for="CDN_INCLUDE_FOLDERS">Include folders</label></td>
                    <td>
                        <?php   echo $form->text('CDN_INCLUDE_FOLDERS', array('class' => '', 'size' => '64'), $CDN_INCLUDE_FOLDERS)?><br>
                        <span class="description">Folders to include in static file matching. Use a comma as the delimiter.</span>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row"><label for="CDN_EXCLUDE_STRINGS">Exclude if substring</label></td>
                    <td>
                        <?php   echo $form->text('CDN_EXCLUDE_STRINGS', array('class' => '', 'size' => '64'), $CDN_EXCLUDE_STRINGS)?><br>
                        <span class="description">Excludes something from being rewritten if one of the above strings is found in the match. Use a comma as the delimiter like this, .php, .flv, .do</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="ccm-pane-footer">
    </div>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <?php   echo  $ui->submit(t('Save'), 'save','right','btn-primary'); ?>
        </div>
    </div>
</form>
