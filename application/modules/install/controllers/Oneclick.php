<?php
/**
 * @copyright Ilch 2.0
 */

namespace Modules\Install\Controllers;

use Ilch\Config\File as File;

class Oneclick extends \Ilch\Controller\Frontend
{
    public function init()
    {
        $fileConfig = new File();
        $fileConfig->loadConfigFromFile(CONFIG_PATH.'/config.php');
        if ($fileConfig->get('dbUser') !== null and $this->getRequest()->getActionName() !== 'finish') {
            /*
             * Cms is installed
             */
            $this->redirect()->to();
        } else {
            /*
             * Cms not installed yet.
             */

            //Configuration
            if ($this->getRequest()->getParam('language')) {
                $_SESSION['language'] = $this->getRequest()->getParam('language');
            } else {
                $_SESSION['language'] = 'de_DE';
            }
            if ($this->getRequest()->getParam('dbEngine')) {
                $_SESSION['install']['dbEngine'] = $this->getRequest()->getParam('dbEngine');
            } else {
                $_SESSION['install']['dbEngine'] = 'Mysql';
            }
            if ($this->getRequest()->getParam('dbPrefix')) {
                $_SESSION['install']['dbPrefix'] = $this->getRequest()->getParam('dbPrefix');
            } else {
                $_SESSION['install']['dbPrefix'] = 'localhost';
            }
            if ($this->getRequest()->getParam('dbName')) {
                $_SESSION['install']['dbName'] = $this->getRequest()->getParam('dbName');
            }
            if ($this->getRequest()->getParam('dbName')) {
                $_SESSION['install']['dbUser'] = $this->getRequest()->getParam('dbUser');
            }

            $_SESSION['install']['timezone'] = SERVER_TIMEZONE;
            $_SESSION['install']['dbPrefix'] = 'ilch_';

            //Admin User
            $_SESSION['install']['adminName'] = 'Admin';
            $_SESSION['install']['adminPassword'] = '1234';
            $_SESSION['install']['adminEmail'] = 'noreply@ilch.de';

            $this->getLayout()->setFile('modules/install/layouts/oneclick');

            /*
             * Dont set a time limit for installer.
             */
            @set_time_limit(0);
        }
    }

    public function indexAction()
    {
        //Check System
        $errors = [];
        if (!version_compare(phpversion(), '5.6.0', '>=')) {
            $errors['wrongPHPVersion'] = true;
        }
        if (!is_writable(ROOT_PATH)) {
            $errors['writableRootPath'] = '/';
        }
        if (!is_writable(APPLICATION_PATH)) {
            $errors['writableConfig'] = '/application/';
        }
        if (!is_writable(ROOT_PATH.'/backups/')) {
            $errors['writableBackups'] = '/backups/';
        }
        if (!is_writable(ROOT_PATH.'/updates/')) {
            $errors['writableUpdates'] = '/updates/';
        }
        if (!is_writable(ROOT_PATH.'/.htaccess')) {
            $errors['writableHtaccess'] = '/.htaccess';
        }
        if (!is_writable(APPLICATION_PATH.'/modules/media/static/upload/')) {
            $errors['writableMedia'] = '/modules/media/static/upload/';
        }
        if (!is_writable(APPLICATION_PATH.'/modules/smilies/static/img/')) {
            $errors['writableMedia'] = '/modules/smilies/static/img/';
        }
        if (!is_writable(APPLICATION_PATH.'/modules/user/static/upload/avatar/')) {
            $errors['writableAvatar'] = '/modules/user/static/upload/avatar/';
        }
        if (!is_writable(APPLICATION_PATH.'/modules/user/static/upload/gallery/')) {
            $errors['writableAvatar'] = '/modules/user/static/upload/gallery/';
        }
        if (!is_writable(ROOT_PATH.'/certificate/')) {
            $errors['writableCertificate'] = '/certificate/';
        }
        if (!extension_loaded('mysqli')) {
            $errors['missingMysqliExtension'] = 'mysqli';
        }
        if (!extension_loaded('mbstring')) {
            $errors['missingMbstringExtension'] = 'mbstring';
        }
        if (!extension_loaded('zip')) {
            $errors['missingZipExtension'] = 'zip';
        }
        if (!extension_loaded('openssl')) {
            $errors['missingOpensslExtension'] = 'openssl';
        }
        if (!extension_loaded('curl')) {
            $errors['missingCURLExtension'] = 'curl';
        }
        if (file_exists(ROOT_PATH.'/certificate/Certificate.crt')) {
            if (!array_key_exists('opensslExtensionMissing', $errors)) {
                $public_key = file_get_contents(ROOT_PATH.'/certificate/Certificate.crt');
                $certinfo = openssl_x509_parse($public_key);
                $validTo = $certinfo['validTo_time_t'];
                if ($validTo < time()) {
                    $errors['expiredCert'] = true;
                }
            }
        } else {
            $errors['certMissing'] = true;
        }

        if ($this->getRequest()->isPost()) {
            $_SESSION['install']['dbName'] = $this->getRequest()->getPost('dbName');
            $_SESSION['install']['dbUser'] = $this->getRequest()->getPost('dbUser');
            $_SESSION['install']['dbPassword'] = $this->getRequest()->getPost('dbPassword');

            if (empty($_SESSION['install']['dbName'])) {
                $errors['dbName'] = 'fieldEmpty';
            }
            if (empty($_SESSION['install']['dbUser'])) {
                $errors['dbUser'] = 'fieldEmpty';
            }

            //Test database connection
            $ilch = new \Ilch\Database\Factory();
            $db = $ilch->getInstanceByEngine($_SESSION['install']['dbEngine']);
            $hostParts = explode(':', $_SESSION['install']['dbHost']);
            $port = null;

            if (!empty($hostParts[1])) {
                $port = $hostParts[1];
            }

            try {
                $db->connect(
                    reset($hostParts),
                    $this->getRequest()->getPost('dbUser'),
                    $this->getRequest()->getPost('dbPassword'),
                    $port
                );

                $selectDb = $db->setDatabase($_SESSION['install']['dbName']);
            } catch (\RuntimeException $ex) {
                $errors['dbConnectionError'] = true;
            }

            if (empty($errors['dbConnectionError'])) {
                try {
                    $db->connect(
                        reset($hostParts),
                        $this->getRequest()->getPost('dbUser'),
                        $this->getRequest()->getPost('dbPassword'),
                        $port
                    );

                    $selectDb = $db->setDatabase($_SESSION['install']['dbName']);
                } catch (\RuntimeException $ex) {
                    $errors['dbConnectionError'] = true;
                }

                if (!$selectDb) {
                    $errors['dbDatabaseDoesNotExist'] = true;
                }
            }

            if (empty($errors)) {
                //Write install config.
                $fileConfig = new \Ilch\Config\File();
                $fileConfig->set('dbEngine', $_SESSION['install']['dbEngine']);
                $fileConfig->set('dbHost', $_SESSION['install']['dbHost']);
                $fileConfig->set('dbUser', $_SESSION['install']['dbUser']);
                $fileConfig->set('dbPassword', $_SESSION['install']['dbPassword']);
                $fileConfig->set('dbName', $_SESSION['install']['dbName']);
                $fileConfig->set('dbPrefix', $_SESSION['install']['dbPrefix']);
                $fileConfig->saveConfigToFile(CONFIG_PATH . '/config.php');

                //Initialize install database.
                $dbFactory = new \Ilch\Database\Factory();
                $db = $dbFactory->getInstanceByConfig($fileConfig);
                \Ilch\Registry::set('db', $db);

                $modulesToInstall = [
                    'admin',
                    'article',
                    'user',
                    'media',
                    'comment',
                    'imprint',
                    'contact',
                    'privacy',
                    'statistic',
                    'cookieconsent',
                    'smilies',

                    'checkoutbasic',
                    'war',
                    'history',
                    'rule',
                    'teams',
                    'training',
                    'calendar',
                    'forum',
                    'guestbook',
                    'link',
                    'linkus',
                    'partner',
                    'shoutbox',
                    'gallery',
                    'downloads',
                    'newsletter',
                    'birthday',
                    'events',
                    'away',
                    'awards',
                    'jobs',
                    'faq',
                    'vote'
                ];

                //Clear old tables.
                $db->dropTablesByPrefix($db->getPrefix());

                $moduleMapper = new \Modules\Admin\Mappers\Module();
                $boxMapper = new \Modules\Admin\Mappers\Box();
                foreach ($modulesToInstall as $module) {
                    $configClass = '\\Modules\\' . ucfirst($module) . '\\Config\\Config';
                    $config = new $configClass($this->getTranslator());
                    $config->install();

                    if (!empty($config->config)) {
                        if ($config->config['key'] != 'admin') {
                            $moduleModel = new \Modules\Admin\Models\Module();
                            $moduleModel->setKey($config->config['key']);
                            if (isset($config->config['author'])) {
                                $moduleModel->setAuthor($config->config['author']);
                            }
                            if (isset($config->config['link'])) {
                                $moduleModel->setLink($config->config['link']);
                            }
                            if (isset($config->config['languages'])) {
                                foreach ($config->config['languages'] as $key => $value) {
                                    $moduleModel->addContent($key, $value);
                                }
                            }
                            if (isset($config->config['system_module'])) {
                                $moduleModel->setSystemModule(true);
                            }
                            if (isset($config->config['version'])) {
                                $moduleModel->setVersion($config->config['version']);
                            }
                            $moduleModel->setIconSmall($config->config['icon_small']);
                            $moduleMapper->save($moduleModel);
                        }

                        if (isset($config->config['boxes'])) {
                            $boxModel = new \Modules\Admin\Models\Box();
                            $boxModel->setModule($config->config['key']);
                            foreach ($config->config['boxes'] as $key => $value) {
                                $boxModel->addContent($key, $value);
                            }
                            $boxMapper->install($boxModel);
                        }
                    }
                }

                $menuMapper = new \Modules\Admin\Mappers\Menu();
                $menu1 = new \Modules\Admin\Models\Menu();
                $menu1->setId(1);
                $menu1->setTitle('Hauptmenü');
                $menuMapper->save($menu1);

                $menu2 = new \Modules\Admin\Models\Menu();
                $menu2->setId(2);
                $menu2->setTitle('Hauptmenü 2');
                $menuMapper->save($menu2);

                $sort = 0;
                $menuItem = new \Modules\Admin\Models\MenuItem();
                $menuItem->setMenuId(1);
                $menuItem->setParentId(0);
                $menuItem->setTitle('Menü');
                $menuItem->setType(0);
                $menuMapper->saveItem($menuItem);

                //Will not linked in menu
                foreach ($modulesToInstall as $module) {
                    if (in_array($module, ['comment', 'shoutbox', 'admin', 'media', 'newsletter', 'statistic', 'cookieconsent', 'error', 'smilies'])) {
                        continue;
                    }

                    $configClass = '\\Modules\\' . ucfirst($module) . '\\Config\\Config';
                    $config = new $configClass($this->getTranslator());

                    $menuItem = new \Modules\Admin\Models\MenuItem();
                    $menuItem->setMenuId(1);
                    $menuItem->setSort($sort);
                    $menuItem->setParentId(1);
                    $menuItem->setType(3);
                    $menuItem->setModuleKey($config->config['key']);
                    $menuItem->setTitle($config->config['languages'][$this->getTranslator()->getLocale()]['name']);
                    $menuMapper->saveItem($menuItem);
                    $sort += 10;
                }

                $boxes = "INSERT INTO `[prefix]_menu_items` (`menu_id`, `sort`, `parent_id`, `page_id`, `box_id`, `box_key`, `type`, `title`, `href`, `module_key`) VALUES
                    (1, 80, 0, 0, 0, 'user_login', 4, 'Login', '', ''),
                    (1, 90, 0, 0, 0, 'admin_layoutswitch', 4, 'Layout', '', ''),
                    (1, 100, 0, 0, 0, 'statistic_stats', 4, 'Statistik', '', ''),
                    (1, 110, 0, 0, 0, 'statistic_online', 4, 'Online', '', ''),
                    (2, 10, 0, 0, 0, 'admin_langswitch', 4, 'Sprache', '', ''),
                    (2, 20, 0, 0, 0, 'article_article', 4, 'Letzte Artikel', '', ''),
                    (2, 30, 0, 0, 0, 'article_archive', 4, 'Archiv', '', ''),
                    (2, 40, 0, 0, 0, 'article_categories', 4, 'Kategorien', '', ''),
                    (2, 50, 0, 0, 0, 'article_keywords', 4, 'Keywords', '', '')";
                $db->queryMulti($boxes);

                //Remove session data
                unset($_SESSION['install']);

                $this->redirect()->to();
            }
        }

        foreach (['dbName', 'dbUser', 'dbPassword'] as $name) {
            if (!empty($_SESSION['install'][$name])) {
                $this->getView()->set($name, $_SESSION['install'][$name]);
            }
        }
        $this->getView()->set('errors', $errors);
    }
}
