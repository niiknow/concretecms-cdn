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
        <div class="form-group">
          <label class="control-label"><?php echo t('Enable Use CDN'); ?></label>
          <div class="checkbox"><?php echo $form->checkbox('CDN_ENABLED', 1, $CDN_ENABLED); ?><?php echo $form->label('CDN_ENABLED', t('Enable CDN Support')); ?><br>
<?php echo t('Note: this plugin uses RegEx replace to manipulate HTML and may result in higher Time To First Byte (TTFB).  Additionally, it will not work in all cases such as dynamic assets loading or referencing of static assets by "url()" inside of CSS file. Therefore, this plugin should only be use with Pull-Zone type of CDN where content must be available at origin. We recommend that you use Concrete5 FlySystem driver to integrate into S3 or Push-Zone CDN for remote FTP.  For popular sites, this plugin help improve/alleviate traffic congestion by serving static content from Offsite/CDN.'); ?>
          </div>
        </div>

        <div class="form-group">
            <?php echo $form->label('CDN_OFFSITE_URL', t('Off-site URL')); ?>
            <?php echo $form->text('CDN_OFFSITE_URL', $CDN_OFFSITE_URL, array('class' => '', 'size' => '64')); ?><br />
            <span class="description">
<?php echo t('The new URL to be used in place of'); ?>
<code><?php echo $SITE_URL; ?></code> <?php echo t('for rewriting. No trailing forward slash (/) please.'); ?>
<br /><?php echo t('Example:'); ?>
<code><?php echo $CDN_OFFSITE_URL; ?>/packages/fundamental/themes/fundamental/css/normalize.min.css</code>.
            </span>
        </div>

        <div class="form-group">
            <?php echo $form->label('CDN_INCLUDE_PATH', t('Include path prefixes')); ?>
            <?php echo $form->text('CDN_INCLUDE_PATH', $CDN_INCLUDE_PATH, array('class' => '', 'size' => '64')); ?><br />
            <span class="description">
<?php echo t('Path prefixes to include in static file matching. Use a comma as the delimiter. Trailing forward slash (/) is OK, '); ?>
<?php echo t('but do not prefix with forward slash (/) please.'); ?> <br />
<?php echo t('Note: you may need to update the default path above to include your subdirectory.  If your root path is example.com/subdirectory, then it should be:'); ?> <br>
<code>subdirectory/application/files/, subdirectory/concrete/, subdirectory/fundamental/, subdirectory/download_file/, subdirectory/packages/</code>
            </span>
        </div>

        <div class="form-group">
            <?php echo $form->label('CDN_EXCLUDE_SUBSTRINGS', t('Exclude if substring')); ?>
            <?php echo $form->text('CDN_EXCLUDE_SUBSTRINGS', $CDN_EXCLUDE_SUBSTRINGS, array('class' => '', 'size' => '64')); ?><br />
            <span class="description">
<?php echo t('Excludes something from being rewritten if one of the above strings is found in the match. '); ?>
<?php echo t('Use a comma as the delimiter.'); ?> <br />
<?php echo t('Note: remember to set Access-Control-Allow-Origin header at CDN to support '); ?>
<?php echo t('contents such as html, json, or fonts (.html, .json, .woff, .ttf)'); ?>
            </span>
        </div>

        <div class="form-group">
            <label class="control-label"><?php echo t('HTML Optimization'); ?></label>
            <div class="checkbox">
           <?php echo $form->checkbox('CDN_MINIFY_HTML', 1, $CDN_MINIFY_HTML); ?> <?php echo $form->label('CDN_MINIFY_HTML', t('Output minified HTML')); ?><br/>
  <span class="description">
<?php echo t('This removes all spacing, comments, etc... This is useful for when testing with Google PageSpeed.'); ?> <br />
<?php echo t('Disclaimer: there is a chance that this may mess up your HTML.  It will also slightly increase Time To First Byte (TTFB).'); ?>
            </span>
        </div>
    </div>
    <div class="ccm-pane-footer">
    </div>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <?php echo $ui->submit(t('Save'), 'save', 'right', 'btn-primary'); ?>
        </div>
    </div>
</form>
