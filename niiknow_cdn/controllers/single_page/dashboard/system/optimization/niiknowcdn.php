<?php
namespace Concrete\Package\NiiknowCdn\Controller\SinglePage\Dashboard\System\Optimization;

defined('C5_EXECUTE') or die(_("Access Denied."));

use Package;
use \Concrete\Core\Page\Controller\DashboardPageController;

class NiiknowCdn extends DashboardPageController
{
    public function view()
    {
        $pkg = Package::getByHandle('niiknow_cdn');
        $this->set('CDN_ENABLED', $pkg->isEnabled());
        $this->set('CDN_OFFSITE_URL', $pkg->getOffsiteUrl());
        $this->set('CDN_INCLUDE_PATH', str_replace('|', ', ', $pkg->getIncludePath()));
        $this->set('CDN_EXCLUDE_SUBSTRINGS', str_replace('|', ', ', $pkg->getExcludeSubstrings()));
        $this->set('CDN_MINIFY_HTML', $pkg->minifyHtml());

        // output current site base url for use in UI
        $this->set('SITE_URL', $pkg->getSiteUrl());
    }

    public function success()
    {
        $this->set('success', t('Configuration updated.'));
        $this->view();
    }

    public function saveSettings()
    {
        $pkg = Package::getByHandle('niiknow_cdn');
        $pkg->setEnabled($this->post('CDN_ENABLED'));
        $pkg->setOffsiteUrl($this->post('CDN_OFFSITE_URL'));
        $pkg->setIncludePath($this->post('CDN_INCLUDE_PATH'));
        $pkg->setExcludeSubstrings($this->post('CDN_EXCLUDE_SUBSTRINGS'));
        $pkg->setMinifyHtml($this->post('CDN_MINIFY_HTML'));

        // redirect to success message
        $this->redirect('/dashboard/system/optimization/niiknowcdn/success');
    }
}
