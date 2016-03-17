<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoload431aec8f2856f20eeabc24e93f0ff16a($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'svnplugin' => '/svnPlugin.class.php',
            'svnplugindescriptor' => '/SvnPluginDescriptor.class.php',
            'svnplugininfo' => '/SvnPluginInfo.class.php',
            'tuleap\\svn\\accesscontrol\\accesscontrolcontroller' => '/Svn/AccessControl/AccessControlController.php',
            'tuleap\\svn\\accesscontrol\\accesscontrolpresenter' => '/Svn/AccessControl/AccessControlPresenter.php',
            'tuleap\\svn\\accesscontrol\\accessfilehistory' => '/Svn/AccessControl/AccessFileHistory.php',
            'tuleap\\svn\\accesscontrol\\accessfilehistorycreator' => '/Svn/AccessControl/AccessFileHistoryCreator.php',
            'tuleap\\svn\\accesscontrol\\accessfilehistorydao' => '/Svn/AccessControl/AccessFileHistoryDao.php',
            'tuleap\\svn\\accesscontrol\\accessfilehistoryfactory' => '/Svn/AccessControl/AccessFileHistoryFactory.php',
            'tuleap\\svn\\accesscontrol\\accessfilehistorynotfoundexception' => '/Svn/AccessControl/AccessFileHistoryNotFoundException.php',
            'tuleap\\svn\\accesscontrol\\accessfilereader' => '/Svn/AccessControl/AccessFileReader.php',
            'tuleap\\svn\\accesscontrol\\cannotcreateaccessfilehistoryexception' => '/Svn/AccessControl/CannotCreateAccessFileHistoryException.php',
            'tuleap\\svn\\accesscontrol\\nullaccessfilehistory' => '/Svn/AccessControl/NullAccessFileHistory.php',
            'tuleap\\svn\\admin\\admincontroller' => '/Svn/Admin/AdminController.class.php',
            'tuleap\\svn\\admin\\cannotcreateimmuabletagexception' => '/Svn/Admin/CannotCreateImmuableTagException.php',
            'tuleap\\svn\\admin\\cannotcreatemailheaderexception' => '/Svn/Admin/CannotCreateMailHeaderException.class.php',
            'tuleap\\svn\\admin\\cannotdeletemailnotificationexception' => '/Svn/Admin/CannotDeleteMailNotificationException.php',
            'tuleap\\svn\\admin\\immutabletag' => '/Svn/Admin/ImmutableTag.php',
            'tuleap\\svn\\admin\\immutabletagcontroller' => '/Svn/Admin/ImmutableTagController.php',
            'tuleap\\svn\\admin\\immutabletagcreator' => '/Svn/Admin/ImmutableTagCreator.php',
            'tuleap\\svn\\admin\\immutabletagdao' => '/Svn/Admin/ImmutableTagDao.php',
            'tuleap\\svn\\admin\\immutabletagfactory' => '/Svn/Admin/ImmutableTagFactory.php',
            'tuleap\\svn\\admin\\immutabletagpresenter' => '/Svn/Admin/ImmutableTagPresenter.php',
            'tuleap\\svn\\admin\\mailheader' => '/Svn/Admin/MailHeader.class.php',
            'tuleap\\svn\\admin\\mailheaderdao' => '/Svn/Admin/MailHeaderDao.class.php',
            'tuleap\\svn\\admin\\mailheadermanager' => '/Svn/Admin/MailHeaderManager.class.php',
            'tuleap\\svn\\admin\\mailnotification' => '/Svn/Admin/MailNotification.class.php',
            'tuleap\\svn\\admin\\mailnotificationdao' => '/Svn/Admin/MailNotificationDao.class.php',
            'tuleap\\svn\\admin\\mailnotificationmanager' => '/Svn/Admin/MailNotificationManager.class.php',
            'tuleap\\svn\\admin\\mailnotificationpresenter' => '/Svn/Admin/MailNotificationPresenter.class.php',
            'tuleap\\svn\\admin\\mailreceivedfromuserextractor' => '/Svn/Admin/MailReceivedFromUserExtractor.php',
            'tuleap\\svn\\admin\\mailreference' => '/Svn/Admin/MailReference.class.php',
            'tuleap\\svn\\admin\\sectionspresenter' => '/Svn/Admin/SectionsPresenter.php',
            'tuleap\\svn\\commit\\cannotfindsvncommitinfoexception' => '/Svn/Commit/CannotFindSVNCommitInfoException.class.php',
            'tuleap\\svn\\commit\\commitinfo' => '/Svn/Commit/CommitInfo.class.php',
            'tuleap\\svn\\commit\\commitinfoenhancer' => '/Svn/Commit/CommitInfoEnhancer.php',
            'tuleap\\svn\\commit\\svnlook' => '/Svn/Commit/Svnlook.class.php',
            'tuleap\\svn\\dao' => '/Svn/Dao.class.php',
            'tuleap\\svn\\eventrepository\\systemevent_svn_create_repository' => '/events/SystemEvent_SVN_CREATE_REPOSITORY.class.php',
            'tuleap\\svn\\explorer\\explorercontroller' => '/Svn/Explorer/ExplorerController.class.php',
            'tuleap\\svn\\explorer\\explorerpresenter' => '/Svn/Explorer/ExplorerPresenter.class.php',
            'tuleap\\svn\\explorer\\repositorydisplaycontroller' => '/Svn/Explorer/RepositoryDisplayController.class.php',
            'tuleap\\svn\\explorer\\repositorydisplaypresenter' => '/Svn/Explorer/RepositoryDisplayPresenter.class.php',
            'tuleap\\svn\\hooks\\postcommit' => '/Svn/Hooks/PostCommit.class.php',
            'tuleap\\svn\\hooks\\precommit' => '/Svn/Hooks/PreCommit.php',
            'tuleap\\svn\\reference\\extractor' => '/Reference/Extractor.php',
            'tuleap\\svn\\reference\\reference' => '/Reference/Reference.php',
            'tuleap\\svn\\repository\\cannotcreaterepositoryexception' => '/Svn/Repository/CannotCreateRepositoryException.class.php',
            'tuleap\\svn\\repository\\cannotfindrepositoryexception' => '/Svn/Repository/CannotFindRepositoryException.class.php',
            'tuleap\\svn\\repository\\repository' => '/Svn/Repository/Repository.class.php',
            'tuleap\\svn\\repository\\repositorymanager' => '/Svn/Repository/RepositoryManager.class.php',
            'tuleap\\svn\\repository\\repositoryregexpbuilder' => '/Svn/Repository/RepositoryRegexpBuilder.php',
            'tuleap\\svn\\repository\\rulename' => '/Svn/Repository/RuleName.class.php',
            'tuleap\\svn\\servicesvn' => '/Svn/ServiceSvn.class.php',
            'tuleap\\svn\\svnlogger' => '/Svn/SvnLogger.php',
            'tuleap\\svn\\svnrouter' => '/Svn/SvnRouter.class.php',
            'tuleap\\svn\\usercannotadministraterepositoryexception' => '/Svn/UserCannotAdministrateRepositoryException.php',
            'tuleap\\svn\\viewvcproxy\\viewvcproxy' => '/Svn/ViewVCProxy/ViewVCProxy.class.php',
            'tuleap\\svn\\xmlimporter' => '/Svn/XMLImporter.class.php',
            'tuleap\\svn\\xmlimporterexception' => '/Svn/XMLImporterException.class.php',
            'tuleap\\svn\\xmlrepositoryimporter' => '/Svn/XMLRepositoryImporter.class.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoload431aec8f2856f20eeabc24e93f0ff16a');
// @codeCoverageIgnoreEnd
