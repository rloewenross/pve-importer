# PVE-Importer
A minimal web app for importing disk images into Proxmox Virtual Environment in a user-friendly, flexible way.

# Usage
After installation, simply log in using your proxmox username and domain (e.g. root@pam) and passsword. Then browse your files and upload your disk image. The installation progress will be shown on the right. PVE-Importer supports qcow2, raw, and vmdk formats, including in zip files.

# Installation
An Ansible role and example playbook is included and is the recommended method of installation. To install simply apply the role to a host with PVE installed and set the appropriate variables (an example is in group_vars.example). The authorative list of variables that can be set to configure the Ansible role is shown in `ansible/roles/pve_importer/defaults/main.yaml`.
Example:
```bash
$ apt install git ansible
$ git clone https://github.com/rloewenross/pve-importer.git
$ cd pve-importer/ansible
$ vi inventory.yaml
$ mkdir -p group_vars/pve_importer
$ vi group_vars/pve_importer/vars.yaml
$ ansible-vault create -J group_vars/pve_importer/vault.yaml
$ ansible-galaxy install -r requirements.yaml
$ ansible-playbook -i inventory.yaml -J deploy.yaml
```


**Disclaimer:** This is an independent project and not affiliated with Proxmox Server Solutions GmbH.