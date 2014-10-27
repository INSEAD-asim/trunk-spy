<?php

/**
 * Handles fetching svn commit log and can store them into a file
 *
 * @author Lysender
 * @package trunk-spy
 */
class Svn_Agent
{
    protected $_emailSender;
    protected $_emailSubject;
    protected $_username;
    protected $_password;
    protected $_storagePath;
    protected $_svnConfigPath;

    protected $_project;
    protected $_svnPath;
    protected $_label;
    protected $_emailRecipients;
    protected $_postUpdate;

    protected $_storedRevision;
    protected $_currentRevision;

    public function __construct($project, array $config, array $globalConfig = array())
    {
        // For project
        $this->_project = $project;

        // For global config
        foreach ($globalConfig as $key => $value) {
            $field          = '_' . $key;
            $this->{$field} = $value;
        }

        // For specific config
        foreach ($config as $key => $value) {
            $field          = '_' . $key;
            $this->{$field} = $value;
        }
    }

    /**
     * Returns the previously stored revision
     *
     * @return Svn_Revision | null
     */
    public function getPrevious()
    {
        $revision = null;
        $xmlFile  = $this->_storagePath . $this->_project . '.xml';

        if (is_file($xmlFile)) {
            $str = file_get_contents($xmlFile);

            try {
                $revision = new Svn_Revision($str);
            } catch (Exception $e) {
                $revision = null;
            }
        }

        return $revision;
    }

    /**
     * Returns the latest revision fresh from SVN
     *
     * @return Svn_Revision | null
     */
    public function getLatest()
    {
        $revision      = null;
        $command       = null;
        $xmlTmpFile    = $this->_storagePath . $this->_project . '_tmp.xml';
        $svnConfigPath = $this->_svnConfigPath . $this->_project;

        if ($this->_username && $this->_password) {
            $command = 'svn log %s --config-dir="%s" --username="%s" --password="%s" --limit=1 --xml 2>&1 > %s';
            $command = sprintf(
                $command,
                $this->_svnPath,
                $svnConfigPath,
                $this->_username,
                $this->_password,
                $xmlTmpFile
            );
        } else {
            $command = 'svn log %s --config-dir="%s" --limit=1 --xml 2>&1 > %s';
            $command = sprintf($command, $this->_svnPath, $svnConfigPath, $xmlTmpFile);
        }

        $result = exec($command, $return);
//        var_dump($result);
//        var_dump($return);

        if (is_file($xmlTmpFile)) {
            $str = file_get_contents($xmlTmpFile);
            $del = unlink($xmlTmpFile);

            try {
                $revision = new Svn_Revision($str);
            } catch (Exception $e) {
                $revision = null;
            }
        }

        return $revision;
    }

    /**
     * Writes the latest revision into file
     *
     * @param Svn_Revision
     * @return Svn_Agent
     */
    public function writeLatest(Svn_Revision $revision)
    {
        $xmlFile = $this->_storagePath . $this->_project . '.xml';

        file_put_contents($xmlFile, $revision->xmlString);

        return $this;
    }

    public function notifyUsers(Svn_Revision $revision)
    {
        $to      = implode(',', $this->_emailRecipients);
        $subject = $this->_emailSubject;
        $subject = sprintf($subject, $this->_label, $revision->author);

        $mail = new Mail($this->_emailSender);

        $message[] = 'Details:';
        $message[] = '========';
        $message[] = 'SVN Path: ' . $this->_svnPath;
        $message[] = 'Revision: ' . $revision->revision;
        $message[] = 'Author: ' . $revision->author;
        $message[] = 'Date: ' . $revision->date;
        $message[] = 'Message: ' . $revision->msg;

        $mail->send($to, $subject, implode("\r\n", $message));
    }

    public function postUpdate()
    {
        $to            = implode(',', $this->_emailRecipients);
        $svnConfigPath = $this->_svnConfigPath . $this->_project;

        $mail = new Mail($this->_emailSender);

        if ($this->_postUpdate != null) {
            $report  = $this->_storagePath . $this->_project . '-post-update-report.txt';
            $script  = $this->_postUpdate['script'];
            $subject = sprintf($this->_postUpdate['subject'], $this->_project);
            $reports = is_array($this->_postUpdate['reports']) ? $this->_postUpdate['reports'] : array();

            if ($this->_username && $this->_password) {
                $command = $script . ' ' . $report . ' ' . $svnConfigPath . ' ' . $this->_username . ' ' . $this->_password;
            } else {
                $command = $script . ' ' . $report . ' ' . $svnConfigPath;
            }

            $result = exec($command, $return);
//            var_dump($result);
//            var_dump($return);

            if (is_file($report)) {
                $str = file_get_contents($report);

                $mail->send($to, $subject, $str, $reports);
            }
        }
    }
}
