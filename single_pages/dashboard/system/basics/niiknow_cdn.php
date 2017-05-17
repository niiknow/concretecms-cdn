<?php defined('C5_EXECUTE') or die("Access Denied.");
$form   = \Core::make('helper/form');
$ui     = \Core::make('helper/concrete/ui');
$dash   = \Core::make('helper/concrete/dashboard');
$action = $this->action('save_settings');
?>
<style>
td { padding-left: 5px !important;}
</style>
<form id="NiiknowCdn-settings" method="post" action="<?php echo $action; ?>">
    <div class="clearfix ccm-pane-body">
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th style="text-align: right"></th>
                    <td scope="row"><?php echo $form->checkbox('CDN_ENABLED', 1, $CDN_ENABLED); ?>
                    <?php echo $form->label('CDN_ENABLED', t('Enable CDN Support')); ?><br/><br/><br/></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="CDN_OFFSITE_URL">Off-site URL</label></th>
                    <td>
<?php echo $form->text('CDN_OFFSITE_URL', $CDN_OFFSITE_URL, array('class' => '', 'size' => '64')); ?>
                        <span class="description">The new URL to be used in place of
                        <code><?php echo t($SITE_URL); ?></code> for rewriting. No trailing
                        <code>/</code> please. <br>Example: <code>
<?php echo t($CDN_OFFSITE_URL); ?>/packages/fundamental/themes/fundamental/css/normalize.min.css</code>
                        .</span><br/><br/><br/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="CDN_INCLUDE_FOLDERS">Include folders</label></th>
                    <td>
<?php
echo $form->text('CDN_INCLUDE_FOLDERS', $CDN_INCLUDE_FOLDERS, array('class' => '', 'size' => '64'));
?>
                        <span class="description">Folders to include in static file matching.
                        Use a comma as the delimiter.
                        Trailing <code>/</code> OK, but do not prefix with <code>/</code> please.</span><br/><br/><br/>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="CDN_EXCLUDE_SUBSTRINGS">Exclude if substring</label></th>
                    <td>
<?php
echo $form->text('CDN_EXCLUDE_SUBSTRINGS', $CDN_EXCLUDE_SUBSTRINGS, array('class' => '', 'size' => '64'));
?>
                        <span class="description">Excludes something from being rewritten if one of the above strings
                        is found in the match. Use a comma as the delimiter like this, .php, .flv, .do.
                        Note: remember to set header 'Access-Control-Allow-Origin' at CDN if you're not
                        excluding html or fonts (.woff, .ttf, etc..) </span><br/><br/><br/>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="ccm-pane-footer">
    </div>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <?php echo $ui->submit(t('Save'), 'save', 'right', 'btn-primary'); ?>
        </div>
    </div>
</form>
