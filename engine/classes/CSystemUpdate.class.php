<?php

/*
 * Class CSystemUpdate
 * Description: Update system files, templates, plugins with RPC from GitHub
*/

class CSystemUpdate
{

    private $updateURL = null;
    private $downloadDest = null;
    private $downloadFile = null;
    private $action = null;
    private $download_stream = null;
    private $answer = [];

    /**
     * @param $params
       - $params['url'] - update url
       - $params['name'] - download file base name
       - $params['action'] - action
         - removed
         - added
         - modified
         - renamed
     * @return Update
     */
    public function __construct($params)
    {
        try
        {
            Lang::load('files');
            
            global $userROW;

            // Check for permissions
            if (!is_array($userROW) or checkPermission(array('plugin' => '#admin', 'item' => 'configuration'), null, 'modify')) {
                throw new CSystemUpdateException(__('perm.denied'), 1);
            }

            if ($params['token'] != genUToken('core.system.update')) {
                throw new CSystemUpdateException(__('wrong_security_code'), 3);
            }

            if(empty($params['url']) or empty($params['name']) or empty($params['action'])) {
                throw new CSystemUpdateException(__('wrong_params_type'), 2);
            }

            ignore_user_abort(true);

            $this->updateURL = $params['url'];
            // full path to file
            $this->downloadDest = site_root . pathinfo($params['name'])['dirname'];
            // full path to file + fileName
            $this->downloadFile = site_root . $params['name'];
            // actions performed on the file
            $this->action = $params['action'];

            $this->download();

        } catch (CSystemUpdateException $e) {
            return $e->errorMessage();
        }
    }

    public function execute()
    {
        return $this->answer;
    }

    private function download()
    {
        try
        {
            if(('removed' == $this->action)) {
                if(!$this->fileRemove($this->downloadFile) and is_file($this->downloadFile)) {
                    throw new CSystemUpdateException('Unable to remove destination file');
                }
            } else {
                if(!file_exists($this->downloadDest) and !mkdir($this->downloadDest, 0644, true)) {
                    throw new CSystemUpdateException('Unable to creat destination directory <b>' . $this->downloadDest . '</b>');
                }
                if(NULL == ($fdest = $this->fileOpen($this->downloadFile, "w+"))) {
                    throw new CSystemUpdateException('Unable to creat destination file <b>' . $this->downloadFile . '</b>');
                }
                if(extension_loaded('curl') and function_exists('curl_init')) {
                    $ch = null;
                    if (!($ch = curl_init()) ){
                        throw new CSystemUpdateException('err_curlinit');
                    }
                    if ( curl_errno($ch) != 0 ){
                        throw new CSystemUpdateException('err_curlinit'.curl_errno($ch).' '.curl_error($ch));
                    }
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_URL, $this->updateURL);
                    $data = curl_exec($ch);
                    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    if(404 == $status) {
                        $this->action = '<b class="text-muted">not found</b>';
                        if(!$this->fileRemove($this->downloadFile) and is_file($this->downloadFile)) {
                            //throw new CSystemUpdateException('Source file not found');
                        }
                    } elseif($status != 200){
                        throw new CSystemUpdateException("Заблудилася я {$this->updateURL} " . $status);
                    }
                    curl_close($ch);
                    fwrite($fdest, $data);
                } elseif(stream_is_local($this->updateURL) or ini_get('allow_url_fopen')) {
                    if(NULL == ($fsrc = $this->fileOpen($this->updateURL, 'r'))) {
                        $this->action = '<b class="text-muted">not found</b>';
                        if(!$this->fileRemove($this->downloadFile) and is_file($this->downloadFile)) {
                            //throw new CSystemUpdateException('Source file not found');
                        }
                    } else {
                        stream_copy_to_stream($fsrc, $fdest);
                        fclose($fsrc);
                    }
                } else {
                    throw new CSystemUpdateException('Not supported: cURL, allow_fopen_url');
                }
                fclose($fdest);
            }

            $this->answer = array( 'status' => 1, 'errorCode' => 0, 'msg' => $this->action, 'file' => str_replace(site_root, '' , $this->downloadFile));

        } catch (CSystemUpdateException $e) {
            return $e->errorMessage();
        }
    }

    private function fileOpen($filename, $mode, $retry = 5)
    {
        while(!($fp = @fopen($filename, $mode))) {
            if(--$retry>0){
                sleep(1);
            } else {
                break;
            }
        }
        return $fp;
    }

    private function fileRemove($filename)
    {
        return @unlink($filename);
    }
}

class CSystemUpdateException extends Exception {
    public function errorMessage() {
        die(json_encode(array('status' => 0, 'errorCode' => ($this->getCode() ? $this->getCode() : 999), 'errorText' => $this->getMessage())));
        coreNormalTerminate(1);
        exit;
    }
}
