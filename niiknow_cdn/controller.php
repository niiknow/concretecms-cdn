<?php
namespace Concrete\Package\NiiknowCdn;

defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page as Page;
use Concrete\Core\Page\Single as SinglePage;
use Concrete\Core\Support\Facade\Events;
use Concrete\Core\Support\Facade\Facade;
use Permissions;

class Controller extends Package
{
    protected $pkgHandle          = 'niiknow_cdn';
    protected $appVersionRequired = '5.7.0.4';
    protected $pkgVersion         = '0.1.3';

    public function getPackageDescription()
    {
        return t('Serve static content with CDN/Offsite Url');
    }

    public function getPackageName()
    {
        return t('Use CDN');
    }

    public function isEnabled()
    {
        return $this->getConfig()->get('niiknow_cdn.enabled') == 1 ? true : false;
    }

    public function setEnabled($enabled = false)
    {
        $this->getConfig()->save('niiknow_cdn.enabled', $enabled ? 1 : 0);
    }

    public function getOffsiteUrl()
    {
        return $this->getConfig()->get('niiknow_cdn.offsite_url');
    }

    public function setOffsiteUrl($arg1 = 'http://your.cdn-url.com')
    {
        $this->getConfig()->save('niiknow_cdn.offsite_url', preg_replace('/[\s\/]*$/', '', $arg1));
    }

    public function getIncludePath()
    {
        return $this->getConfig()->get('niiknow_cdn.include_path');
    }

    public function setIncludePath($arg1 = 'application/files/,concrete/,fundamental/,download_file/,packages/')
    {
        $this->getConfig()->save('niiknow_cdn.include_path',
            str_replace(',', '|', preg_replace('/\s*/', '', $arg1)));
    }

    public function getExcludeSubstrings()
    {
        return $this->getConfig()->get('niiknow_cdn.exclude_substring');
    }


    public function setExcludeSubstrings($arg1 = '.php,.htm')
    {
        $this->getConfig()->save('niiknow_cdn.exclude_substring',
            str_replace(',', '|', preg_replace('/\s*/', '', $arg1)));
    }

    public function minifyHtml()
    {
        return $this->getConfig()->get('niiknow_cdn.minifyhtml') == 1 ? true : false;
    }

    public function setMinifyHtml($enabled = false)
    {
        $this->getConfig()->save('niiknow_cdn.minifyhtml', $enabled ? 1 : 0);
    }

    public function getSiteUrl()
    {
       
        $app = Facade::getFacadeApplication();
        $base_uri = $app->make('url/canonical');
        return $base_uri;
    }

    public function install()
    {
        $pkg = parent::install();

        // init default values
        $this->setEnabled();
        $this->setOffsiteUrl();
        $this->setIncludePath();
        $this->setExcludeSubstrings();
        $this->setMinifyHtml();

        $single_page = SinglePage::add('/dashboard/system/optimization/niiknowcdn', $pkg);
        $single_page->update(array('cName' => t('CDN Settings'), 'cDescription' => t('CDN Settings')));
    }

    // CSS Minifier => http://ideone.com/Q5USEF + improvement(s)
    private function doCssMinify($input) {
        if(trim($input) === "") return $input;
        return preg_replace(
            array(
                // Remove comment(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
                // Remove unused white-space(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
                // Replace `:0 0 0 0` with `:0`
                '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
                // Replace `background-position:0` with `background-position:0 0`
                '#(background-position):0(?=[;\}])#si',
                // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                '#(?<=[\s:,\-])0+\.(\d+)#s',
                // Minify string value
                '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
                // Minify HEX color code
                '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                // Replace `(border|outline):none` with `(border|outline):0`
                '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
                // Remove empty selector(s)
                '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
            ),
            array(
                '$1',
                '$1$2$3$4$5$6$7',
                '$1',
                ':0',
                '$1:0 0',
                '.$1',
                '$1$3',
                '$1$2$4$5',
                '$1$2$3',
                '$1:0',
                '$1$2'
            ),
        $input);
    }

    // JavaScript Minifier
    private function doJsMinify($input) {
        if(trim($input) === "") return $input;
        return preg_replace(
            array(
                // Remove comment(s)
                '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
                // Remove white-space(s) outside the string and regex
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
                // Remove the last semicolon
                '#;+\}#',
                // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
                '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
                // --ibid. From `foo['bar']` to `foo.bar`
                '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
            ),
            array(
                '$1',
                '$1$2',
                '}',
                '$1$3',
                '$1.$3'
            ),
        $input);
    }

    /**
     * -----------------------------------------------------------------------------------------
     * Based on `https://github.com/mecha-cms/mecha-cms/blob/master/system/kernel/converter.php`
     * https://gist.github.com/Rodrigo54/93169db48194d470188f
     * -----------------------------------------------------------------------------------------
     */
    public function doHtmlMinify($input) {
        if(trim($input) === "") return $input;

        // Remove extra white-space(s) between HTML attribute(s)
        $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
            return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
        }, str_replace("\r", "", $input));

        // minify inline style and script tags
        if(strpos($input, '</style>') !== false) {
          $input = preg_replace_callback('#<style(.*?)>(.*?)</style>#is', function($matches) {
            return '<style' . $matches[1] .'>'. $this->doCssMinify($matches[2]) . '</style>';
          }, $input);
        }

        if(strpos($input, '</script>') !== false) {
          $input = preg_replace_callback('#<script(.*?)>(.*?)</script>#is', function($matches) {
            return '<script' . $matches[1] .'>'. $this->doJsMinify($matches[2]) . '</script>';
          }, $input);
        }

        return preg_replace(
            array(
                // t = text
                // o = tag open
                // c = tag close
                // Keep important white-space(s) after self-closing HTML tag(s)
                '#<(img|input)(>| .*?>)#s',
                // Remove a line break and two or more white-space(s) between tag(s)
                '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
                '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
                '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
                '#<(img|input)(>| .*?>)<\/\1>#s', // reset previous fix
                '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
                '#(?<=\>)(&nbsp;)(?=\<)#', // --ibid
                // Remove HTML comment(s) except IE comment(s)
                '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
            ),
            array(
                '<$1$2</$1>',
                '$1$2$3',
                '$1$2$3',
                '$1$2$3$4$5',
                '$1$2$3$4$5$6$7',
                '$1$2$3',
                '<$1$2',
                '$1 ',
                '$1',
                ""
            ),
        $input);
    }

    public function on_start()
    {
        Events::addListener('on_page_output', function ($event) {
            $cp = new Permissions(Page::getCurrentPage());
            if ($cp->canViewToolbar()) {
                return;
            }
            $that = Package::getByHandle('niiknow_cdn');

            if ($that->isEnabled()) {
                $baseUrl      = $that->getSiteUrl();
                $baseUrlParts = parse_url($baseUrl);
                $site_domain  = $baseUrlParts["host"];

                $baseUrlReg = '/\s+(href|src)\=([\"\'])https?:\/\/(' . $site_domain . ')\//umi';
                $cdnUrl     = $that->getOffsiteUrl();

                // Get the page content from event instance
                $contents = $event->getArgument('contents');

                // perform CDN replace
                // 1. replace full URL with simply forward slash (/)
                $contents = preg_replace($baseUrlReg, " $1=$2/", $contents);

               // 2. replace forward slash with __CDN_URL__
                $includeFolders = $that->getIncludePath();
                if ($includeFolders) {
                    $includeFolders = str_replace('/', '\/', $includeFolders);

                    $cdnUrlReg = '/\s+(href|src|srcset)\=([\"\'])\/('.$includeFolders.')/umi';
                    $contents = preg_replace($cdnUrlReg, " $1=$2__CDNURL__/$3", $contents);

                    // 2-2. regex for inline url
                    $inlineReg = '/(\s*url\s*\(\s*[\'\"]?)(\/)('.$includeFolders.')+([^\)]+)/umi';
                    $contents = preg_replace($inlineReg, "$1__CDNURL__$2$3$4", $contents);

                    // 3. replace excluded back to just relative URL
                    $excludeSubstrings = $that->getExcludeSubstrings();
                    if ($excludeSubstrings) {
                        $excludeSubstrings = str_replace('/', '\/', $excludeSubstrings);

                        $cdnUrlReg = '/(__CDNURL__)(.*)('.$excludeSubstrings.')/umi';
                        $contents = preg_replace($cdnUrlReg, "$2$3", $contents);
                    }
                }

                // finally update __CDNURL__ to the real URL
                $contents = str_replace('__CDNURL__', $cdnUrl, $contents);

                // minify html
                if ($that->minifyHtml()) {
                    $contents = $that->doHtmlMinify($contents);
                }

                // output signature
                // $contents = $contents.'<!-- UseCDN: '.gmdate("Y-m-d\TH:i:s\Z").' -->';

                // Back the replaced content to the event instance
                $event->setArgument('contents', $contents);
            }
        });
    }
}
