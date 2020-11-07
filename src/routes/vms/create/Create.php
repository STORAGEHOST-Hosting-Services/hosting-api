<?php

namespace Vms;

use PDO;

include "model/vmsCreateModel.php";

class Create
{
    private array $vm_data;
    private PDO $pdo;
    private array $valid_vm_data;
    private string $error_vm_data;

    public function __construct(array $vm_data, PDO $pdo)
    {
        $this->vm_data = $vm_data;
        $this->pdo = $pdo;
        $this->error_vm_data = "";
    }

    public function validateData()
    {
        // Check if all data are present
        if ($this->vm_data && $this->vm_data['username'] && $this->vm_data['password'] && $this->vm_data['instance_type'] && $this->vm_data['os'] && $this->vm_data['user_id']) {

            if (filter_var($this->vm_data['username'], FILTER_SANITIZE_STRING)) {
                $this->valid_vm_data['username'] = $this->vm_data['username'];
            } else {
                $this->error_vm_data = "username";
            }

            // Add password in the valid array
            $this->valid_vm_data['password'] = $this->vm_data['password'];

            // Check if the provided values match instance type
            $instance_type = $this->vm_data['instance_type'];
            if ($instance_type == "s1.sm" || $instance_type == "s1.md" || $instance_type == "s1.lg" || $instance_type == "s2.sm" || $instance_type == "s2.md" || $instance_type == "s2.lg") {
                $this->valid_vm_data['instance_type'] = $instance_type;
            } else {
                $this->error_vm_data = "bad_instance_type";
            }

            // Check the OS
            $os = $this->vm_data['os'];
            if ($os == "debian10" || $os == "centos8" || $os == "ubuntu2004" || $os == "winsrv") {
                $this->valid_vm_data['os'] = $os;
            } else {
                $this->error_vm_data = "bad_os";
            }

            // Get a free IP from the database
            $this->valid_vm_data['ip'] = $this->getIp();

            // Get a free name from the database
            $this->valid_vm_data['hostname'] = $this->getName((string)$this->valid_vm_data['os']);

            // Check if the provided values match the standard system values
            /**if ($cpu > 0 && $ram > 0) {
             * if ($instance_type == "s1.sm") {
             * if ($cpu = 1 && $system['ram'] = 1000) {
             * $this->valid_vm_data['system']['cpu'] = $cpu;
             * $this->valid_vm_data['system']['ram'] = $ram;
             *
             * if ($storage['size'] = 10) {
             * $this->valid_vm_data['storage'] = $storage;
             *
             *
             * } else {
             * $this->error_vm_data = "bad_disk";
             * }
             *
             * } else {
             * $this->error_vm_data = "bad_instance_type";
             * }
             *
             * } elseif ($instance_type == "s1.md") {
             * if ($cpu = 2 && $system['ram'] = 2000) {
             * $this->valid_vm_data['system']['cpu'] = $cpu;
             * $this->valid_vm_data['system']['ram'] = $ram;
             *
             * if ($storage['size'] = 10) {
             * $this->valid_vm_data['storage'] = $storage;
             * } else {
             * $this->error_vm_data = "bad_disk";
             * }
             *
             * } else {
             * $this->error_vm_data = "bad_instance_type";
             * }
             * } elseif ($instance_type == "s1.lg") {
             * if ($cpu = 2 && $system['ram'] = 4000) {
             * $this->valid_vm_data['system']['cpu'] = $cpu;
             * $this->valid_vm_data['system']['ram'] = $ram;
             *
             * if ($storage['size'] = 10) {
             * $this->valid_vm_data['storage'] = $storage;
             * } else {
             * $this->error_vm_data = "bad_disk";
             * }
             *
             * } else {
             * $this->error_vm_data = "bad_instance_type";
             * }
             * } elseif ($instance_type == "s2.sm") {
             * if ($cpu = 4 && $system['ram'] = 8000) {
             * $this->valid_vm_data['system']['cpu'] = $cpu;
             * $this->valid_vm_data['system']['ram'] = $ram;
             *
             * if ($storage['size'] = 15) {
             * $this->valid_vm_data['storage'] = $storage;
             * } else {
             * $this->error_vm_data = "bad_disk";
             * }
             *
             * } else {
             * $this->error_vm_data = "bad_instance_type";
             * }
             * } elseif ($instance_type == "s2.md") {
             * if ($cpu = 4 && $system['ram'] = 16000) {
             * $this->valid_vm_data['system']['cpu'] = $cpu;
             * $this->valid_vm_data['system']['ram'] = $ram;
             *
             * if ($storage['size'] = 20) {
             * $this->valid_vm_data['storage'] = $storage;
             * } else {
             * $this->error_vm_data = "bad_disk";
             * }
             *
             * } else {
             * $this->error_vm_data = "bad_instance_type";
             * }
             * } else {
             * if ($cpu = 6 && $system['ram'] = 32000) {
             * $this->valid_vm_data['system']['cpu'] = $cpu;
             * $this->valid_vm_data['system']['ram'] = $ram;
             *
             * if ($storage['size'] = 50) {
             * $this->valid_vm_data['storage'] = $storage;
             * } else {
             * $this->error_vm_data = "bad_disk";
             * }
             *
             * } else {
             * $this->error_vm_data = "bad_instance_type";
             * }
             * }
             * } else {
             * $this->error_vm_data = "system";
             * }
             *
             * // Get the IP and add it to the array
             * $this->valid_vm_data['ip'] = $this->checkIp();
             *
             * var_dump($this->valid_vm_data);
             * return "";*/

            var_dump($this->valid_vm_data);

            if ($this->error_vm_data !== "") {
                return $this->error_vm_data;
            } else {
                //var_dump($this->valid_vm_data);
                return $this->valid_vm_data;
            }


        } else {
            return "missing_requested_parameter";
        }
    }

    private function createInstanceName()
    {
        // Since the instance name depends on the OS type, this function will create a name according to the OS. It will then
    }

    private function getIp()
    {
        // Get the IP from the DB table
         return (new vmsCreateModel($this->pdo))->getIp();
    }

    private function getName(string $os)
    {
        return (new vmsCreateModel($this->pdo))->getName($os);
    }

    public
    function createVm()
    {
        return (new vmsCreateModel($this->pdo))->createVm($this->vm_data);
    }
}