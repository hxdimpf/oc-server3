<?php
/***************************************************************************
 * for license information see LICENSE.md
 *  Inherit Smarty-Class and extend it
 ***************************************************************************/

use Oc\GeoCache\Enum\GeoCacheType;
use Oc\Util\CBench;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db.inc.php';
require_once __DIR__ . '/logic/labels.inc.php';

/**
 * Class OcSmarty
 */
class OcSmarty extends Smarty
{
    public string $_var_bracket_regexp = '';
    public string $_num_const_regexp = '';
    public string $_db_qstr_regexp = '';
    public string $_dvar_math_regexp = '';
    public string $_dvar_math_var_regexp = '';
    public string $_dvar_guts_regexp = '';
    public string $_dvar_regexp = '';
    public string $_obj_restricted_param_regexp = '';
    public string $_obj_single_param_regexp = '';
    public string $_mod_regexp = '';
    public string $_func_regexp = '';
    public string $_cvar_regexp = '';
    public string $_svar_regexp = '';
    public string $_avar_regexp = '';
    public string $_si_qstr_regexp = '';
    public string $_var_regexp = '';
    public string $_obj_ext_regexp = '';
    public string $_obj_start_regexp = '';
    public string $_obj_params_regexp = '';
    public string $_obj_call_regexp = '';
    public string $_reg_obj_regexp = '';
    public string $_param_regexp = '';
    public string $_parenth_param_regexp = '';
    public string $_func_call_regexp = '';
    public string $_qstr_regexp = '';

    public string $name = 'sys_nothing';

    public string $main_template = 'sys_main';

    public $bench = null;

    public $compile_id = null;

    public $cache_id = null;    // This is a smarty caching ID, not a caches.cache_id.

    public string $title = '';

    public $menuitem = null;

    public bool $nowpsearch = false;

    public bool $change_country_inpage = false;

    // no header, menu or footer
    public bool $popup = false;

    // show a thin border when using popup
    // disable popupmargin to appear fullscreen
    public bool $popupmargin = true;

    // url to call if login is required
    public string $target = '';

    public array $header_javascript = [];

    public array $body_load = [];

    public array $body_unload = [];

    /**
     * OcSmarty constructor.
     *
     * @throws SmartyException
     */
    public function __construct()
    {
        parent::__construct();
        global $opt;
        $this->bench = new CBench();
        $this->bench->start();

        // in Smarty 3 fehlende Variablen einbauen..
        $this->setMissingSmartyVariables();

        // configuration
        $this->template_dir = $opt['stylepath'];
        $this->compile_dir = __DIR__ . '/../var/cache2/smarty/compiled/';
        $this->cache_dir = __DIR__ . '/../var/cache2/smarty/cache/';
        $this->config_dir = __DIR__ . '/../config2/';
        $this->plugins_dir = [__DIR__ . '/../src/OcLegacy/SmartyPlugins', __DIR__ . '/../vendor/smarty/smarty/libs/plugins', __DIR__ . '/../vendor/smarty/smarty/libs/sysplugins'];

        if (!is_dir(__DIR__ . '/../var/cache2/smarty/cache/')) {
            mkdir(__DIR__ . '/../var/cache2/smarty/cache/');
        }
        if (!is_dir(__DIR__ . '/../var/cache2/smarty/compiled/')) {
            mkdir(__DIR__ . '/../var/cache2/smarty/compiled/');
        }

        // disable caching ... if caching is enabled, 1 hour is default
        $this->caching = 0;
        $this->cache_lifetime = 3600; // default

        // register additional functions
        require_once __DIR__ . '/../src/OcLegacy/SmartyPlugins/block.nocache.php';
//        $this->register_block('nocache', 'smarty_block_nocache', 'smarty_block_nocache'); // TODO: Smarty? Was ist der Ersatz dafür?
        $this->loadFilter('pre', 't');

        // cache control
        if (($opt['debug'] & DEBUG_TEMPLATES) == DEBUG_TEMPLATES) {
            $this->force_compile = true;
        }

        // site maintenance
        if (($opt['debug'] & DEBUG_OUTOFSERVICE) == DEBUG_OUTOFSERVICE) {
            $this->name = 'sys_outofservice';
            $this->display();
        }

        /* set login target
         */
        if (isset($_REQUEST['target'])) {
            $this->target = trim($_REQUEST['target']);
            if (preg_match('/^https?:/i', $this->target)) {
                $this->target = '';
            }
        } else {
            $target = basename($_SERVER['PHP_SELF']) . '?';

            // REQUEST-Variablen durchlaufen und an target anhaengen
            foreach ($_REQUEST as $varname => $varvalue) {
                if (in_array($varname, $opt['logic']['targetvars'])) {
                    $target .= urlencode($varname) . '=' . urlencode($varvalue) . '&';
                }
            }

            if (mb_substr($target, - 1) == '?' || mb_substr($target, - 1) == '&') {
                $target = mb_substr($target, 0, - 1);
            }

            $this->target = $target;
        }
    }

    /* ATTENTION: copied from internal implementation!
     * @param string $resource_name
     * @param string $compile_id
     */
    /**
     *
     * @throws SmartyException
     */
    public function compile($resource_name, $compile_id = null)
    : void {
        if (!isset($compile_id)) {
            $compile_id = $this->compile_id;
        }

        // TODO: was ist jetzt richtig? _compile_id war zuerst da, wurde aber mal angemeckert..
//        $this->_compile_id = $compile_id;
        $this->compile_id = $compile_id;
        $this->setCompileId($compile_id);

        // load filters that are marked as autoload
        if (count($this->autoload_filters)) {
            foreach ($this->autoload_filters as $_filter_type => $_filters) {
                foreach ($_filters as $_filter) {
                    $this->loadFilter($_filter_type, $_filter);
                }
            }
        }

        $_smarty_compile_path = $this->getCompileDir();
    }

    /**
     * @param null|mixed $dummy1
     * @param null|mixed $dummy2
     * @param null|mixed $dummy3
     * @param null|mixed $dummy4
     *
     * @throws SmartyException
     */
    public function display($dummy1 = null, $dummy2 = null, $dummy3 = null, $dummy4 = null)
    : void {
        global $opt, $db, $cookie, $login, $menu, $sqldebugger, $translate, $useragent_msie;
        $cookie->close();

        // if the user is an admin, don't cache the content
        if (isset($login)) {
            if ($login->admin) {
                $this->caching = 0;
            }
        }

        //Give Smarty access to the whole options array.
        $this->assign('siteSettings', $opt);
        $this->assign('GeoCacheTypeEvent', GeoCacheType::EVENT);

        //Should we remove this whole block since we now have
        //access using the siteSettings above?
        // assign main template vars
        // ... and some of the $opt
        $locale = $opt['template']['locale'];

        $optn = [];
        $optn['debug'] = $opt['debug'];
        $optn['template']['locales'] = $opt['template']['locales'];
        $optn['template']['locale'] = $opt['template']['locale'];
        $optn['template']['style'] = $opt['template']['style'];
        $optn['template']['country'] = $login->getUserCountry();
        $optn['page']['subtitle1'] = $opt['locale'][$locale]['page']['subtitle1'] ?? $opt['page']['subtitle1'];
        $optn['page']['subtitle2'] = $opt['locale'][$locale]['page']['subtitle2'] ?? $opt['page']['subtitle2'];
        $optn['page']['sitename'] = $opt['page']['sitename'];
        $optn['page']['headimagepath'] = $opt['page']['headimagepath'];
        $optn['page']['headoverlay'] = $opt['page']['headoverlay'];
        $optn['page']['max_logins_per_hour'] = $opt['page']['max_logins_per_hour'];
        $optn['page']['absolute_url'] = $opt['page']['absolute_url'];
        $optn['page']['absolute_urlpath'] = parse_url($opt['page']['absolute_url'], PHP_URL_PATH);
        $optn['page']['absolute_http_url'] = $opt['page']['absolute_http_url'];
        $optn['page']['default_absolute_url'] = $opt['page']['default_absolute_url'];
        $optn['page']['login_url'] = ($opt['page']['https']['force_login'] ? $opt['page']['absolute_https_url'] : '') . 'login.php';
        $optn['page']['target'] = $this->target;
        $optn['page']['showdonations'] = $opt['page']['showdonations'];
        $optn['page']['title'] = $opt['page']['title'];
        $optn['page']['nowpsearch'] = $this->nowpsearch;
        $optn['page']['header_javascript'] = $this->header_javascript;
        $optn['page']['body_load'] = $this->body_load;
        $optn['page']['body_unload'] = $this->body_unload;
        $optn['page']['sponsor'] = $opt['page']['sponsor'];
        $optn['page']['showsocialmedia'] = $opt['page']['showsocialmedia'];
        $optn['page']['main_country'] = $opt['page']['main_country'];
        $optn['page']['main_locale'] = $opt['page']['main_locale'];
        $optn['page']['meta'] = $opt['page']['meta'];
        $optn['page']['teampic_url'] = $opt['page']['teampic_url'];
        $optn['page']['teammember_url'] = $opt['page']['teammember_url'];
        $optn['template']['title'] = $this->title;
        $optn['template']['caching'] = $this->caching;
        $optn['template']['popup'] = $this->popup;
        $optn['template']['popupmargin'] = $this->popupmargin;
        $optn['format'] = $opt['locale'][$opt['template']['locale']]['format'];
        $optn['mail'] = $opt['mail'];
        $optn['lib'] = $opt['lib'];
        $optn['tracking'] = $opt['tracking'];
        $optn['geokrety'] = $opt['geokrety'];
        $optn['template']['usercountrieslist'] = labels::getLabels('usercountrieslist');
        $optn['help']['oconly'] = helppagelink('oconly', 'OConly');
        $optn['msie'] = $useragent_msie;

        $loginn = [
            'username' => '',
            'userid' => '',
            'admin' => '',
        ];

        if (isset($login)) {
            $loginn['username'] = $login->username;
            $loginn['userid'] = $login->userid;
            $loginn['admin'] = $login->admin;
        }

        // build menu
        if ($this->menuitem == null) {
            $menu->SetSelectItem(MNU_ROOT);
        } else {
            $menu->SetSelectItem($this->menuitem);
        }

        $this->assign('topmenu', $menu->getTopMenu());
        $this->assign('submenu', $menu->getSubMenu());
        $this->assign('breadcrumb', $menu->getBreadcrumb());
        $this->assign('menucolor', $menu->getMenuColor());
        $this->assign('helplink', helppagelink($this->name));
        $this->assign('change_country_inpage', $this->change_country_inpage);

        if ($this->title == '') {
            $optn['template']['title'] = $menu->GetMenuTitle();
        }

        // build address for switching locales and countries
        $base_pageadr = $_SERVER['REQUEST_URI'];

        // workaround for http://redmine.opencaching.de/issues/703
        $strange_things_pos = strpos($base_pageadr, '.php/');
        if ($strange_things_pos) {
            $base_pageadr = substr($base_pageadr, 0, $strange_things_pos + 4);
        }
        $lpos = strpos($base_pageadr, 'locale=');
        if ($this->change_country_inpage) {
            if (!$lpos) {
                $lpos = strpos($base_pageadr, 'usercountry=');
            }
            if (!$lpos) {
                $lpos = strpos($base_pageadr, 'country=');
            }
        }
        if ($lpos) {
            $base_pageadr = substr($base_pageadr, 0, $lpos);
        } else {
            $urx = explode('#', $base_pageadr);
            $base_pageadr = $urx[0];
            if (strpos($base_pageadr, '?') == 0) {
                $base_pageadr .= '?';
            } else {
                $base_pageadr .= '&';
            }
        }
        $this->assign('base_pageadr', $base_pageadr);

        if ($opt['logic']['license']['disclaimer']) {
            $lurl = $opt['locale'][$locale]['page']['license_url'] ?? $opt['locale']['EN']['page']['license_url'];

            if (isset($opt['locale'][$locale]['page']['license'])) {
                $ltext = mb_ereg_replace(
                    '{site}',
                    $opt['page']['sitename'],
                    $opt['locale'][$locale]['page']['license']
                );
            } else {
                $ltext = $opt['locale']['EN']['page']['license'];
            }

            $this->assign('license_disclaimer', mb_ereg_replace('%1', $lurl, $ltext));
        } else {
            $this->assign('license_disclaimer', '');
        }

        $this->assign('opt', $optn);
        $this->assign('login', $loginn);

        if ($db['connected']) {
            $this->assign('sys_dbconnected', true);
        } else {
            $this->assign('sys_dbconnected', false);
        }
        $this->assign('sys_dbslave', ($db['slave_id'] != - 1));

        if ($this->templateExists($this->name . '.tpl')) {
            $this->assign('template', $this->name);
        } elseif ($this->name != 'sys_error') {
            $this->error(ERROR_TEMPLATE_NOT_FOUND);
        }

        $this->bench->stop();
        $this->assign('sys_runtime', $this->bench->diff());

        $this->assign(
            'screen_css_time',
            filemtime(__DIR__ . '/../resource2/' . $opt['template']['style'] . '/css/style_screen.css')
        );
        $this->assign(
            'screen_msie_css_time',
            filemtime(__DIR__ . '/../resource2/' . $opt['template']['style'] . '/css/style_screen_msie.css')
        );
        $this->assign(
            'print_css_time',
            filemtime(__DIR__ . '/../resource2/' . $opt['template']['style'] . '/css/style_print.css')
        );

        // check if the template is compiled
        // if not, check if translation works correct
        $_smarty_compile_path = $this->getCompileDir();

        if ($this->name != 'error') {
            $internal_lang = $translate->t('INTERNAL_LANG', 'all', 'OcSmarty.class.php', '');
            if (($internal_lang != $opt['template']['locale']) && ($internal_lang != 'INTERNAL_LANG')) {
                $this->error(ERROR_COMPILATION_FAILED);
            }
        }

        if ($this->is_cached()) {
            $this->assign('sys_cached', true);
        } else {
            $this->assign('sys_cached', false);
        }

        if ($db['debug'] === true) {
            parent::fetch($this->main_template . '.tpl', $this->get_cache_id(), $this->get_compile_id());

            $this->clearAllAssign();
            $this->main_template = 'sys_sqldebugger';
            $this->assign('commands', $sqldebugger->getCommands());
            $this->assign('cancel', $sqldebugger->getCancel());
            unset($sqldebugger);

            $this->assign('opt', $optn);
            $this->assign('login', $loginn);

            $this->caching = 0;

            // unset sqldebugger to allow proper translation of sqldebugger template
            $opt['debug'] = $opt['debug'] & ~DEBUG_SQLDEBUGGER;

            $this->header();
            parent::display($this->main_template . '.tpl');
        } else {
            $this->header();
            parent::display($this->main_template . '.tpl', $this->get_cache_id(), $this->get_compile_id());
        }

        exit;
    }

    /**
     * show an error dialog
     *
     * @param int $id
     *
     * @throws SmartyException
     */
    public function error(int $id)
    : void {
        $this->clearAllAssign();
        $this->caching = 0;

        $this->assign('page', $this->name);
        $this->assign('id', $id);

        if ($this->menuitem == null) {
            $this->menuitem = MNU_ERROR;
        }

        $args = func_get_args();
        unset($args[0]);
        for ($i = 1; isset($args[$i]); $i ++) {
            $this->assign('p' . $i, $args[$i]);
        }

        $this->name = 'error';
        $this->display();
    }

    /**
     * check if this template is valid
     *
     * @param null $tpl_file
     * @param null $cache_id
     * @param null $compile_id
     *
     * @return bool
     * @throws SmartyException
     */
    public function is_cached($tpl_file = null, $cache_id = null, $compile_id = null): bool
    {
        global $login;

        // if the user is an admin, dont cache the content
        if (isset($login)) {
            if ($login->admin) {
                return false;
            }
        }

        return parent::isCached($this->main_template . '.tpl', $this->get_cache_id(), $this->get_compile_id());
    }

    /**
     * @return string
     */
    public function get_cache_id()
    : string
    {
        // $cache_id can be directly supplied from unverified user input (URL params).
        // Probably this is no safety or stability issue, but to be sure we restrict
        // the ID to a reasonable set of characters:

        return $this->name . '|' . mb_ereg_replace('/[^A-Za-z0-9_\|\-\.]/', '', $this->cache_id);
    }

    /**
     * @return string
     */
    public function get_compile_id()
    : string
    {
        global $opt;

        return $opt['template']['style'] . '|' . $opt['template']['locale'] . '|' . $this->compile_id;
    }

    /**
     * @param string $page
     */
    public function redirect($page)
    : void {
        global $cookie, $opt;
        $cookie->close();

        // close db-connection
        sql_disconnect();

        $this->header();

        if (strpos($page, "\n") !== false) {
            $page = substr($page, 0, strpos($page, "\n"));
        }

        // redirect
        if (!preg_match('/^https?:/i', $page)) {
            if (substr($page, 0, 1) == '/') {
                $page = substr($page, 1);
            }
            $page = $opt['page']['absolute_url'] . $page;
        }

        header('Location: ' . $page);
        exit;
    }

    /**
     * redirect login function
     */
    public function redirect_login()
    : void
    {
        global $opt;

        // we cannot redirect the POST-data
        if (count($_POST) > 0) {
            $this->error(ERROR_LOGIN_REQUIRED);
        }

        // ok ... redirect the get-data
        $target = ($opt['page']['https']['force_login'] ? 'https' : $opt['page']['protocol'])
                  . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $this->redirect('login.php?target=' . urlencode($target));
    }

    /**
     * @param $name
     * @param $rs
     */
    public function assign_rs($name, $rs)
    : void {
        $items = [];
        while ($r = sql_fetch_assoc($rs)) {
            $items[] = $r;
        }
        $this->assign($name, $items);
    }

    /**
     * @param $src
     */
    public function add_header_javascript($src)
    : void {
        $this->header_javascript[] = $src;
    }

    /**
     * @param $script
     */
    public function add_body_load($script)
    : void {
        $this->body_load[] = $script;
    }

    /**
     * @param $script
     */
    public function add_body_unload($script)
    : void {
        $this->body_unload[] = $script;
    }

    /**
     * setting http header
     */
    public function header()
    : void
    {
        global $opt;
        global $cookie;

        if ($opt['gui'] == GUI_HTML) {
            // charset setzen
            header('Content-type: text/html; charset=utf-8');

            // HTTP/1.1
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', false);
            // HTTP/1.0
            header('Pragma: no-cache');
            // Date in the past
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            // always modified
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

            // set the cookie
            $cookie->header();
        }
    }

    /**
     * - trim target and strip newlines
     * - use sDefault if sTarget is absolute and sDefault!=null
     *
     * @param $sTarget
     * @param null|mixed $sDefault
     * @return null|string
     */
    public function checkTarget($sTarget, $sDefault = null)
    {
        if (mb_strpos($sTarget, "\n") !== false) {
            $sTarget = mb_substr($sTarget, 0, mb_strpos($sTarget, "\n"));
        }

        $sTarget = mb_trim($sTarget);

        if (mb_strtolower(mb_substr($sTarget, 0, 7)) == 'http://' || $sTarget == '') {
            if ($sDefault != null) {
                return $sDefault;
            }
        }

        return $sTarget;
    }

    public function acceptsAndPurifiesHtmlInput()
    : void
    {
        // Prevent false XSS detection of harmless HTML code
        // see https://redmine.opencaching.de/issues/1137
        // see https://stackoverflow.com/questions/43249998/chrome-err-blocked-by-xss-auditor-details

        // XSS protection can be safely disabled if user-supplied content cannot inject JavaScript,
        // see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-XSS-Protection
        // This is ensured by HTMLpurifier in OC code.

        header('X-XSS-Protection: 0');
    }

    // Setze verlustig gegangene Smartyvariablen..
    private function setMissingSmartyVariables() {
        // matches double quoted strings:
        // "foobar"
        // "foo\"bar"
        $this->_db_qstr_regexp = '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"';

        // matches single quoted strings:
        // 'foobar'
        // 'foo\'bar'
        $this->_si_qstr_regexp = '\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'';

        // matches single or double quoted strings
        $this->_qstr_regexp = '(?:' . $this->_db_qstr_regexp . '|' . $this->_si_qstr_regexp . ')';

        // matches bracket portion of vars
        // [0]
        // [foo]
        // [$bar]
        $this->_var_bracket_regexp = '\[\$?[\w\.]+\]';

        // matches numerical constants
        // 30
        // -12
        // 13.22
        $this->_num_const_regexp = '(?:\-?\d+(?:\.\d+)?)';

        // matches $ vars (not objects):
        // $foo
        // $foo.bar
        // $foo.bar.foobar
        // $foo[0]
        // $foo[$bar]
        // $foo[5][blah]
        // $foo[5].bar[$foobar][4]
        $this->_dvar_math_regexp = '(?:[\+\*\/\%]|(?:-(?!>)))';
        $this->_dvar_math_var_regexp = '[\$\w\.\+\-\*\/\%\d\>\[\]]';
        $this->_dvar_guts_regexp = '\w+(?:' . $this->_var_bracket_regexp
                                   . ')*(?:\.\$?\w+(?:' . $this->_var_bracket_regexp . ')*)*(?:' . $this->_dvar_math_regexp . '(?:' . $this->_num_const_regexp . '|' . $this->_dvar_math_var_regexp . ')*)?';
        $this->_dvar_regexp = '\$' . $this->_dvar_guts_regexp;

        // matches config vars:
        // #foo#
        // #foobar123_foo#
        $this->_cvar_regexp = '\#\w+\#';

        // matches section vars:
        // %foo.bar%
        $this->_svar_regexp = '\%\w+\.\w+\%';

        // matches all valid variables (no quotes, no modifiers)
        $this->_avar_regexp = '(?:' . $this->_dvar_regexp . '|'
                              . $this->_cvar_regexp . '|' . $this->_svar_regexp . ')';

        // matches valid variable syntax:
        // $foo
        // $foo
        // #foo#
        // #foo#
        // "text"
        // "text"
        $this->_var_regexp = '(?:' . $this->_avar_regexp . '|' . $this->_qstr_regexp . ')';

        // matches valid object call (one level of object nesting allowed in parameters):
        // $foo->bar
        // $foo->bar()
        // $foo->bar("text")
        // $foo->bar($foo, $bar, "text")
        // $foo->bar($foo, "foo")
        // $foo->bar->foo()
        // $foo->bar->foo->bar()
        // $foo->bar($foo->bar)
        // $foo->bar($foo->bar())
        // $foo->bar($foo->bar($blah,$foo,44,"foo",$foo[0].bar))
        $this->_obj_ext_regexp = '\->(?:\$?' . $this->_dvar_guts_regexp . ')';
        $this->_obj_restricted_param_regexp = '(?:'
                                              . '(?:' . $this->_var_regexp . '|' . $this->_num_const_regexp . ')(?:' . $this->_obj_ext_regexp . '(?:\((?:(?:' . $this->_var_regexp . '|' . $this->_num_const_regexp . ')'
                                              . '(?:\s*,\s*(?:' . $this->_var_regexp . '|' . $this->_num_const_regexp . '))*)?\))?)*)';
        $this->_obj_single_param_regexp = '(?:\w+|' . $this->_obj_restricted_param_regexp . '(?:\s*,\s*(?:(?:\w+|'
                                          . $this->_var_regexp . $this->_obj_restricted_param_regexp . ')))*)';
        $this->_obj_params_regexp = '\((?:' . $this->_obj_single_param_regexp
                                    . '(?:\s*,\s*' . $this->_obj_single_param_regexp . ')*)?\)';
        $this->_obj_start_regexp = '(?:' . $this->_dvar_regexp . '(?:' . $this->_obj_ext_regexp . ')+)';
        $this->_obj_call_regexp = '(?:' . $this->_obj_start_regexp . '(?:' . $this->_obj_params_regexp . ')?(?:' . $this->_dvar_math_regexp . '(?:' . $this->_num_const_regexp . '|' . $this->_dvar_math_var_regexp . ')*)?)';

        // matches valid modifier syntax:
        // |foo
        // |@foo
        // |foo:"bar"
        // |foo:$bar
        // |foo:"bar":$foobar
        // |foo|bar
        // |foo:$foo->bar
        $this->_mod_regexp = '(?:\|@?\w+(?::(?:\w+|' . $this->_num_const_regexp . '|'
                             . $this->_obj_call_regexp . '|' . $this->_avar_regexp . '|' . $this->_qstr_regexp .'))*)';

        // matches valid function name:
        // foo123
        // _foo_bar
        $this->_func_regexp = '[a-zA-Z_]\w*';

        // matches valid registered object:
        // foo->bar
        $this->_reg_obj_regexp = '[a-zA-Z_]\w*->[a-zA-Z_]\w*';

        // matches valid parameter values:
        // true
        // $foo
        // $foo|bar
        // #foo#
        // #foo#|bar
        // "text"
        // "text"|bar
        // $foo->bar
        $this->_param_regexp = '(?:\s*(?:' . $this->_obj_call_regexp . '|'
                               . $this->_var_regexp . '|' . $this->_num_const_regexp  . '|\w+)(?>' . $this->_mod_regexp . '*)\s*)';

        // matches valid parenthesised function parameters:
        //
        // "text"
        //    $foo, $bar, "text"
        // $foo|bar, "foo"|bar, $foo->bar($foo)|bar
        $this->_parenth_param_regexp = '(?:\((?:\w+|'
                                       . $this->_param_regexp . '(?:\s*,\s*(?:(?:\w+|'
                                       . $this->_param_regexp . ')))*)?\))';

        // matches valid function call:
        // foo()
        // foo_bar($foo)
        // _foo_bar($foo,"bar")
        // foo123($foo,$foo->bar(),"foo")
        $this->_func_call_regexp = '(?:' . $this->_func_regexp . '\s*(?:' . $this->_parenth_param_regexp . '))';
    }
}
