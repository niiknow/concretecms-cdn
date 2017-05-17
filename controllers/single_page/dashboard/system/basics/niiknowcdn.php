<?php      
namespace Concrete\Package\Niiknowcdn\Controller\SinglePage\Dashboard\System\Basics;

defined('C5_EXECUTE') or die(_("Access Denied."));

use Package;
use \Concrete\Core\Page\Controller\DashboardPageController;
use Log;

class Niiknowcdn extends DashboardPageController 
{
    function view()
    {
        $pkg = Package::getByHandle('niiknowcdn');
        $this->set( 'CDN_ENABLED', $pkg->isEnabled() );
        $this->set( 'CDN_OFFSITE_URL', $pkg->getOffsiteUrl() );
        $this->set( 'CDN_INCLUDE_FOLDERS', $pkg->getIncludeFolders() );
        $this->set( 'CDN_EXCLUDE_STRINGS', $pkg->getExcludeStrings() );
    }

    function success()
    {
        $this->set('success', t('Configuration updated') );
        $this->view();
    }

    function save_settings()
    {
        $pkg = Package::getByHandle('niiknowcdn');
        $pkg->setEnabled($this->post('CDN_ENABLED'));
        $pkg->setOffsiteUrl($this->post('CDN_OFFSITE_URL'));
        $pkg->setIncludeFolders($this->post('CDN_INCLUDE_FOLDERS'));
        $pkg->setExcludeStrings($this->post('CDN_EXCLUDE_STRINGS'));
        $this->redirect('/dashboard/system/basics/niiknowcdn/success');
    }
}