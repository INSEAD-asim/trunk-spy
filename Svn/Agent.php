<?php

/**
 * Handles fetching svn commit log and can store them into a file
 *
 * @author Lysender
 * @package trunk-spy
 */
class Svn_Agent
{
    protected $_name;
    protected $_username;
    protected $_password;
    protected $_svnPath;
    protected $_label;
    protected $_svnConfigPath;
    protected $_storagePath;
    protected $_notifyRecipients;
    protected $_subjectLine;

    protected $_storedRevision;
    protected $_currentRevision;

    public function __construct($name, array $config, array $globalConfig = array())
    {
        // For project name
        $this->_name = $name;

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
        $revision    = null;
        $storagePath = $this->_storagePath . $this->_name . '.xml';

        if (is_file($storagePath)) {
            $str = file_get_contents($storagePath);

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
        $revision       = null;
        $command        = null;
        $storagePathTmp = $this->_storagePath . $this->_name . '_tmp.xml';
        $svnConfigPath  = $this->_svnConfigPath . $this->_name;

        if ($this->_username && $this->_password) {
            $command = 'svn log %s --config-dir="%s" --username="%s" --password="%s" --limit=1 --xml 2>&1 > %s';
            $command = sprintf(
                $command,
                $this->_svnPath,
                $svnConfigPath,
                $this->_username,
                $this->_password,
                $storagePathTmp
            );
        } else {
            $command = 'svn log %s --config-dir="%s" --limit=1 --xml 2>&1 > %s';
            $command = sprintf($command, $this->_svnPath, $svnConfigPath, $storagePathTmp);
        }

        $result = exec($command, $return);
        var_dump($result);
        var_dump($return);

        if (is_file($storagePathTmp)) {
            $str = file_get_contents($storagePathTmp);

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
        $storagePath = $this->_storagePath . $this->_name . '.xml';

        file_put_contents($storagePath, $revision->xmlString);

        return $this;
    }

    public function notifyUsers(Svn_Revision $revision)
    {
        $to      = implode(',', $this->_notifyRecipients);
        $subject = $this->_subjectLine;
        $subject = sprintf($subject, $this->_label, $revision->author);

        $message[] = 'Details:';
        $message[] = '========';
        $message[] = 'SVN Path: ' . $this->_svnPath;
        $message[] = 'Revision: ' . $revision->revision;
        $message[] = 'Author: ' . $revision->author;
        $message[] = 'Date: ' . $revision->date;
        $message[] = 'Message: ' . $revision->msg;

        mail($to, $subject, implode("\n", $message));
    }
}
