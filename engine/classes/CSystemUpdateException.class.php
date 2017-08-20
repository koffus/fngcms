<?php

/*
 * Class CSystemUpdate
 * Description: Update system files, templates, plugins with RPC from GitHub
*/

class CSystemUpdateException extends Exception {
    public function errorMessage() {
        die(json_encode(array('status' => 0, 'errorCode' => ($this->getCode() ? $this->getCode() : 999), 'errorText' => $this->getMessage())));
        coreNormalTerminate(1);
        exit;
    }
}
