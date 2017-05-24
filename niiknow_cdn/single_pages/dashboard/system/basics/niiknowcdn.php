<?php defined('C5_EXECUTE') or die("Access Denied.");
$form   = \Core::make('helper/form');
$ui     = \Core::make('helper/concrete/ui');
$dash   = \Core::make('helper/concrete/dashboard');
$action = $this->action('saveSettings');
?>
<style>
.my-form-table td { padding-left: 5px !important;}
</style>
<form id="NiiknowCdn-settings" method="post" action="<?php echo $action; ?>">
    <div class="clearfix ccm-pane-body">
        <table class="my-form-table form-table">
            <tbody>
                <tr valign="top">
                    <th style="text-align: right"></th>
                    <td scope="row"><?php echo $form->checkbox('CDN_ENABLED', 1, $CDN_ENABLED); ?>
                    <?php echo $form->label('CDN_ENABLED', t('Enable CDN Support')); ?><br/><br/><br/></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo $form->label('CDN_OFFSITE_URL', t('Off-site URL')); ?></th>
                    <td>
<?php echo $form->text('CDN_OFFSITE_URL', $CDN_OFFSITE_URL, array('class' => '', 'size' => '64')); ?>
                        <span class="description"><?php echo t('The new URL to be used in place of') ?>
                        <code><?php echo $SITE_URL ?></code> <?php echo t('for rewriting. No trailing forward slash (/) please.') ?>
                        <br><?php echo t('Example:') ?>
<code><?php echo $CDN_OFFSITE_URL ?>/packages/fundamental/themes/fundamental/css/normalize.min.css</code>
                        .</span><br/><br/><br/>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo $form->label('CDN_INCLUDE_PATH', t('Include path prefixes')); ?></th>
                    <td>
<?php
echo $form->text('CDN_INCLUDE_PATH', $CDN_INCLUDE_PATH, array('class' => '', 'size' => '64'));
?>
                        <span class="description">
<?php echo t('Path prefixes to include in static file matching. Use a comma as the delimiter. Trailing forward slash (/) is OK,') ?>
<?php echo t('but do not prefix with forward slash (/) please.') ?></span><br/><br/><br/>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php echo $form->label('CDN_EXCLUDE_SUBSTRINGS', t('Exclude if substring')); ?></th>
                    <td>
<?php
echo $form->text('CDN_EXCLUDE_SUBSTRINGS', $CDN_EXCLUDE_SUBSTRINGS, array('class' => '', 'size' => '64'));
?>
                        <span class="description">
<?php echo t('Excludes something from being rewritten if one of the above strings') ?>
<?php echo t('is found in the match. Use a comma as the delimiter.') ?>
<?php echo t('Note: remember to set Access-Control-Allow-Origin header at CDN to support') ?>
<?php echo t('contents such as html, json, or fonts (.html, .json, .woff, .ttf)') ?></span><br/><br/><br/>
                    </td>
                </tr>

                <tr valign="top">
                    <th style="text-align: right"></th>
                    <td scope="row"><?php echo $form->checkbox('CDN_MINIFY_HTML', 1, $CDN_MINIFY_HTML); ?>
                    <?php echo $form->label('CDN_MINIFY_HTML', t('Output minified HTML')); ?>
  <span class="description">
<?php echo t('This removes all spacing, comments, etc...  This is helpful when optimizing for Google PageSpeed.  Disclaimer: there is a chance that this may mess up your HTML.  It will also slightly increase Time To First Byte (TTFB).') ?></span><br/><br/><br/>
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
