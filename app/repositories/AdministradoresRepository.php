<?php
class AdministradoresRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DBC::get();
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT
                    p.idpersona AS idadministrador,
                    CONCAT(p.ap_paterno, ' ', p.ap_materno, ' ', p.nombres) AS nombre,
                    p.ap_paterno,
                    p.ap_materno,
                    p.nombres,
                    p.curp,
                    p.telefono,
                    p.sexo,
                    p.edad,
                    p.email,
                    p.foto_ruta,
                    p.created_at,
                    e.nombre AS estado,
                    m.nombre AS municipio
                FROM personas p
                LEFT JOIN estados e ON e.idestado = p.idestado
                LEFT JOIN municipios m ON m.idmunicipio = p.idmunicipio
                WHERE p.idrol = 1
                  AND p.activo = 1
                ORDER BY p.ap_paterno, p.ap_materno, p.nombres";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $idPersona): ?array
    {
        $sql = "SELECT
                    p.idpersona AS idadministrador,
                    CONCAT(p.ap_paterno, ' ', p.ap_materno, ' ', p.nombres) AS nombre,
                    p.ap_paterno,
                    p.ap_materno,
                    p.nombres,
                    p.email,
                    p.telefono,
                    p.sexo,
                    p.fecha_nacimiento,
                    p.edad,
                    p.curp,
                    p.clave_elector,
                    p.foto_ruta,
                    p.dom_calle,
                    p.dom_numero,
                    p.dom_cruz1,
                    p.dom_cruz2,
                    p.dom_colonia,
                    p.dom_cp,
                    p.idestado,
                    p.idmunicipio,
                    p.dom_referencia,
                    p.activo
                FROM personas p
                WHERE p.idpersona = ?
                  AND p.idrol = 1
                  AND p.activo = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idPersona]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function crearAdministrador(array $data): int
    {
        try {
            $this->db->beginTransaction();

            $sqlPersona = "INSERT INTO personas (
                ap_paterno, ap_materno, nombres,
                email, telefono, sexo, fecha_nacimiento,
                edad, curp, clave_elector, foto_ruta,
                dom_calle, dom_numero, dom_cruz1, dom_cruz2,
                dom_colonia, dom_cp, idestado, idmunicipio, dom_referencia,
                idrol, activo, created_at
            ) VALUES (
                :ap_paterno, :ap_materno, :nombres,
                :email, :telefono, :sexo, :fecha_nacimiento,
                :edad, :curp, :clave_elector, :foto_ruta,
                :dom_calle, :dom_numero, :dom_cruz1, :dom_cruz2,
                :dom_colonia, :dom_cp, :idestado, :idmunicipio, :dom_referencia,
                1, 1, NOW()
            )";

            $stmtPersona = $this->db->prepare($sqlPersona);
            $stmtPersona->execute([
                'ap_paterno' => $data['ap_paterno'],
                'ap_materno' => $data['ap_materno'],
                'nombres' => $data['nombres'],
                'email' => $data['email'],
                'telefono' => $data['telefono'],
                'sexo' => $data['sexo'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'edad' => $data['edad'],
                'curp' => $data['curp'],
                'clave_elector' => $data['clave_elector'],
                'foto_ruta' => $data['foto_ruta'],
                'dom_calle' => $data['dom_calle'],
                'dom_numero' => $data['dom_numero'],
                'dom_cruz1' => $data['dom_cruz1'],
                'dom_cruz2' => $data['dom_cruz2'],
                'dom_colonia' => $data['dom_colonia'],
                'dom_cp' => $data['dom_cp'],
                'idestado' => $data['idestado'],
                'idmunicipio' => $data['idmunicipio'],
                'dom_referencia' => $data['dom_referencia'],
            ]);

            $idPersona = (int)$this->db->lastInsertId();
            $this->db->commit();

            return $idPersona;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizarAdministrador(int $idPersona, array $data): void
    {
        try {
            $sql = "UPDATE personas SET
                        ap_paterno = :ap_paterno,
                        ap_materno = :ap_materno,
                        nombres = :nombres,
                        email = :email,
                        telefono = :telefono,
                        sexo = :sexo,
                        fecha_nacimiento = :fecha_nacimiento,
                        edad = :edad,
                        curp = :curp,
                        clave_elector = :clave_elector,
                        foto_ruta = :foto_ruta,
                        dom_calle = :dom_calle,
                        dom_numero = :dom_numero,
                        dom_cruz1 = :dom_cruz1,
                        dom_cruz2 = :dom_cruz2,
                        dom_colonia = :dom_colonia,
                        dom_cp = :dom_cp,
                        idestado = :idestado,
                        idmunicipio = :idmunicipio,
                        dom_referencia = :dom_referencia
                    WHERE idpersona = :idpersona
                      AND idrol = 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'ap_paterno' => $data['ap_paterno'],
                'ap_materno' => $data['ap_materno'],
                'nombres' => $data['nombres'],
                'email' => $data['email'],
                'telefono' => $data['telefono'],
                'sexo' => $data['sexo'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'edad' => $data['edad'],
                'curp' => $data['curp'],
                'clave_elector' => $data['clave_elector'],
                'foto_ruta' => $data['foto_ruta'],
                'dom_calle' => $data['dom_calle'],
                'dom_numero' => $data['dom_numero'],
                'dom_cruz1' => $data['dom_cruz1'],
                'dom_cruz2' => $data['dom_cruz2'],
                'dom_colonia' => $data['dom_colonia'],
                'dom_cp' => $data['dom_cp'],
                'idestado' => $data['idestado'],
                'idmunicipio' => $data['idmunicipio'],
                'dom_referencia' => $data['dom_referencia'],
                'idpersona' => $idPersona,
            ]);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function eliminarAdministrador(int $idPersona): void
    {
        try {
            $sql = "UPDATE personas
                    SET activo = 0
                    WHERE idpersona = :idpersona
                      AND idrol = 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['idpersona' => $idPersona]);
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
