<?php
/** 
 * Handles fetching svn commit log and can store them into a file
 *
 * @author Lysender
 * @package trunk-spy
 */
class Svn_Agent
{
    protected $_username;
    protected $_password;
    protected $_svnPath;
    protected $_label;
    protected $_storagePath;
    protected $_storagePathTmp;
    protected $_notifyRecipients;

    protected $_storedRevision;
    protected $_currentRevision;

    public function __construct(array $config)
    {
        foreach ($config as $key => $value) {
            $field = '_' . $key;
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

        if (is_file($this->_storagePath)) {
            $str = file_get_contents($this->_storagePath);

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
        $revision = null;
        $command = null;

        if ($this->_username && $this->_password) {
            $command = 'svn log %s --username=%s --password=%s --limit 1 --xml 2>&1 > %s';
            $command = sprintf($command, $this->_svnPath, $this->_storagePathTmp);
        } else {
            $command = 'svn log %s --limit 1 --xml 2>&1 > %s';
            $command = sprintf($command, $this->_svnPath, $this->_storagePathTmp);
        }
        $result = exec($command);

        if (is_file($this->_storagePathTmp)) {
            $str = file_get_contents($this->_storagePathTmp);

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
        file_put_contents($this->_storagePath, $revision->xmlString);

        return $this;
    }

    public function notifyUsers(Svn_Revision $revision)
    {
        $to = implode(',', $this->_notifyRecipients);
        $subject = '%s has been updated by %s';
        $subject = sprintf($subject, $this->_label, $revision->author);

        $message[] = 'Details: ';
        $message[] = 'SVN Path: '.$this->_svnPath;
        $message[] = 'Revision: '.$revision->revision;
        $message[] = 'Author: '.$revision->author;
        $message[] = 'Date: '.$revision->date;
        $message[] = 'Message: '.$revision->msg;

        mail($to, $subject, implode("\n", $message));
    }
}
