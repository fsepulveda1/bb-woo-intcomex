<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

class ImporterResponse {

    private string $status = 'OK';
    private string $action = 'create';
    private $data;
    private array $errors = [];

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     *
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string[] $errors
     */
    public function setErrors(array $errors): void
    {
        if($this->isSuccess()) {
            $this->setStatus('ERROR');
        }

        $this->errors[] = $errors;
    }

    public function addError($error) {
        if($this->isSuccess()) {
            $this->setStatus('ERROR');
        }

        $this->errors[] = $error;
    }

    public function isSuccess(): bool
    {
        return $this->status == 'OK';
    }

    public function isError(): bool
    {
        return $this->status == 'ERROR';
    }

}
