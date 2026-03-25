<?php

namespace App\Models;

use CodeIgniter\Model;

class PerfilModel extends Model
{
    protected $table = 'perfiles';
    protected $primaryKey = 'id_perfil';
    protected $allowedFields = ['nombre'];

    public function getAccesosXPerfiles($idPerfil)
    {
        try {

            return $this->db->table('perfiles_acceso pa')
                ->select('pa.id_perfil_acceso, a.id_acceso, a.acceso')
                ->join('accesos a', 'a.id_acceso = pa.id_acceso')
                ->where('pa.id_perfil', $idPerfil)
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Error getAccesosXPerfiles: ' . $e->getMessage());
            return false;
        }
    }

    public function accesosAll()
    {
        try {
            // Seleccionar todos los accesos desde la tabla 'accesos'
            return $this->db->table('accesos')->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Error accesosAll: ' . $e->getMessage());
            return false;
        }
    }

    public function insertAccesoPerfil($idPerfil, $idAcceso)
    {
        return $this->db->table('perfiles_acceso')->insert([
            'id_perfil' => $idPerfil,
            'id_acceso' => $idAcceso
        ]);
    }


    public function deleteAccesoPerfil($idPerfil, $idAcceso)
    {
        return $this->db->table('perfiles_acceso')
            ->where('id_perfil', $idPerfil)
            ->where('id_acceso', $idAcceso)
            ->delete();
    }
}
