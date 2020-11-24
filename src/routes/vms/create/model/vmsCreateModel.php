<?php

namespace Vms;

use PDO;

class vmsCreateModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createVm(array $vm_data)
    {
        //var_dump($vm_data);
        // Add VM data into the specified tables

        // Add system data
        $req = $this->pdo->prepare('INSERT INTO hosting.`system`(instance_type, os, hypervisor_name) VALUES (:instance_type, :os, :hypervisor_name)');
        $req->execute(array(
            ':instance_type' => $vm_data['instance_type'],
            ':os' => $vm_data['os'],
            ':hypervisor_name' => "swphyvsor01"
        ));

        // Get the last ID
        $id = $this->pdo->lastInsertId();

        // Add storage data
        $req1 = $this->pdo->prepare('INSERT INTO hosting.storage(instance_name) VALUES (:instance_name)');
        $req1->execute(array(
            ':instance_name' => $vm_data['hostname']
        ));

        // Add a new entry into the ip table
        $req3 = $this->pdo->prepare('INSERT INTO hosting.ip(ip, assigned_instance_id) VALUES (:ip, :assigned_instance_id)');
        $req3->execute(array(
            ':ip' => $vm_data['ip'],
            ':assigned_instance_id' => $id
        ));

        // Add networking data
        $req2 = $this->pdo->prepare('INSERT INTO hosting.networking(ip) VALUES (:ip)');
        $req2->execute(array(
            ':ip' => $vm_data['ip']
        ));

        if ($req && $req1 && $req2) {
            // Add system ID
            $vm_data['system_id'] = $this->getLastSystemId();

            // Add storage ID
            $vm_data['storage_id'] = $this->getLastStorageId();

            // Add networking ID
            $vm_data['networking_id'] = $this->getLastNetworkingId();

            var_dump($vm_data);

            //return $this->addVmData($vm_data);
        } else {
            return false;
        }
    }

    private function addVmData(array $vm_data)
    {
        $req = $this->pdo->prepare('INSERT INTO hosting.vms(hostname, username, password, instance_type, state, system_id, storage_id, networking_id, user_id) VALUES (:hostname, :username, :password, :instance_type, :state, :system_id, :storage_id, :networking_id, :user_id)');
        if ($req->execute(array(
            ':hostname' => $vm_data['hostname'],
            ':username' => $vm_data['username'],
            ':password' => password_hash($vm_data['password'], PASSWORD_DEFAULT),
            ':instance_type' => $vm_data['instance_type'],
            ':state' => false,
            ':system_id' => $vm_data['system_id'],
            ':storage_id' => $vm_data['storage_id'],
            ':networking_id' => $vm_data['networking_id'],
            ':user_id' => $vm_data['user_id'],
        ))) {
            //return $this->addVmData($vm_data);
        } else {
            // An error occurred
            return false;
        }
    }

    public function getName(string $os)
    {
        if ($os == "debian10" || $os == "centos8" || $os == "ubuntu2004") {
            $req = $this->pdo->prepare('SELECT * FROM linux_hostname ORDER BY id DESC LIMIT 1');
            $req->execute();

            $last_linux_hostname = $req->fetch(PDO::FETCH_ASSOC);

            // Increment the instance name by adding 1 to the number at the end
            $hostname = "";

            $id = (int)$last_linux_hostname['id'];
            $id = $id + 1;

            if ($id < 10) {
                $hostname = "SLVHOSSOR0" . $id;
            } else {
                $hostname = "SLVHOSSOR" . $id;
            }

            return $hostname;
        } else {
            $req1 = $this->pdo->prepare('SELECT * FROM windows_hostname ORDER BY id DESC LIMIT 1');
            $req1->execute();

            $last_windows_hostname = $req1->fetch(PDO::FETCH_ASSOC);

            // Increment the instance name by adding 1 to the number at the end
            $hostname = "";

            $id = (int)$last_windows_hostname['id'];
            $id = $id + 1;

            if ($id < 10) {
                $hostname = "SWVHOSSOR0" . $id;
            } else {
                $hostname = "SWVHOSSOR" . $id;
            }

            return $hostname;
        }

    }

    public function getIp()
    {
        $req = $this->pdo->prepare('SELECT * FROM ip ORDER BY id DESC LIMIT 1');
        $req->execute();

        $ip = $req->fetch(PDO::FETCH_ASSOC);
        $last_ip = (int)substr($ip['ip'], 9);
        $last_ip = $last_ip + 1;

        // Add the newly generated IP end to the core
        return "172.16.1." . $last_ip;

    }

    private function getLastSystemId()
    {
        $req = $this->pdo->prepare('SELECT * FROM hosting.`system` ORDER BY id DESC LIMIT 1');
        $req->execute();

        return $req->fetch(PDO::FETCH_ASSOC)['id'];
    }

    private function getLastStorageId()
    {
        $req = $this->pdo->prepare('SELECT * FROM hosting.storage ORDER BY id DESC LIMIT 1');
        $req->execute();

        return $req->fetch(PDO::FETCH_ASSOC)['id'];
    }

    private function getLastNetworkingId()
    {
        $req = $this->pdo->prepare('SELECT * FROM hosting.networking ORDER BY id DESC LIMIT 1');
        $req->execute();

        return $req->fetch(PDO::FETCH_ASSOC)['id'];
    }
}