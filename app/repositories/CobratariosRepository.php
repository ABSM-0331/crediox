<?php
class CobratariosRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DBC::get();
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT
                    p.idpersona AS idcobratario,
                    CONCAT(p.ap_paterno, ' ', p.ap_materno, ' ', p.nombres) AS nombre,
                    p.curp,
                    p.telefono,
                    p.sexo,
                    p.edad,
                    e.nombre AS estado,
                    m.nombre AS municipio,

                    -- campos ocultos
                    p.email,
                    p.clave_elector,
                    p.fecha_nacimiento,
                    p.foto_ruta,
                    p.dom_calle,
                    p.dom_numero,
                    p.dom_colonia,
                    p.dom_cp,
                    p.idestado,
                    p.idmunicipio,
                    p.dom_referencia

                FROM personas p
                LEFT JOIN usuarios u   ON u.idpersona = p.idpersona
                JOIN estados e    ON e.idestado = p.idestado
                JOIN municipios m ON m.idmunicipio = p.idmunicipio

                WHERE p.idrol = 3
                AND p.activo = 1
                ORDER BY p.ap_paterno, p.ap_materno, p.nombres";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerTodosConEstadisticas(): array
    {
        $sql = "SELECT
                    p.idpersona AS idcobratario,
                    CONCAT(p.ap_paterno, ' ', p.ap_materno, ' ', p.nombres) AS nombre,
                    p.curp,
                    p.telefono,
                    p.sexo,
                    p.edad,
                    e.nombre AS estado,
                    m.nombre AS municipio,
                    p.email,
                    p.clave_elector,
                    p.fecha_nacimiento,
                    p.foto_ruta,
                    p.dom_calle,
                    p.dom_numero,
                    p.dom_colonia,
                    p.dom_cp,
                    p.idestado,
                    p.idmunicipio,
                    p.dom_referencia,
                    COALESCE(cred.total_clientes, 0) AS clientes_asignados,
                    COALESCE(cred.total_creditos, 0) AS creditos_asignados,
                    COALESCE(cob.total_cobrado, 0) AS total_cobrado
                FROM personas p
                LEFT JOIN usuarios u ON u.idpersona = p.idpersona
                JOIN estados e ON e.idestado = p.idestado
                JOIN municipios m ON m.idmunicipio = p.idmunicipio
                LEFT JOIN (
                    SELECT
                        c.idcobratario,
                        COUNT(*) AS total_creditos,
                        COUNT(DISTINCT c.idcliente) AS total_clientes
                    FROM creditos c
                    INNER JOIN personas cli ON cli.idpersona = c.idcliente AND cli.activo = 1
                    INNER JOIN personas cobf ON cobf.idpersona = c.idcobratario AND cobf.activo = 1
                    GROUP BY c.idcobratario
                ) cred ON cred.idcobratario = p.idpersona
                LEFT JOIN (
                    SELECT
                        c.idcobratario,
                        COALESCE(SUM(hp.monto_pagado), 0) AS total_cobrado
                    FROM historial_pagos hp
                    INNER JOIN creditos c ON c.idcredito = hp.idcredito
                    INNER JOIN personas cli ON cli.idpersona = c.idcliente AND cli.activo = 1
                    INNER JOIN personas cobf ON cobf.idpersona = c.idcobratario AND cobf.activo = 1
                    WHERE hp.fecha_pago = CURDATE()
                    GROUP BY c.idcobratario
                ) cob ON cob.idcobratario = p.idpersona
                WHERE p.idrol = 3
                  AND p.activo = 1
                ORDER BY p.ap_paterno, p.ap_materno, p.nombres";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function crearCobratario(array $data): int
    {
        try {
            $dataPersona = [
                "ap_paterno" => $data["ap_paterno"],
                "ap_materno" => $data["ap_materno"],
                "nombres" => $data["nombres"],
                "email" => $data["email"],
                "telefono" => $data["telefono"],
                "sexo" => $data["sexo"],
                "fecha_nacimiento" => $data["fecha_nacimiento"],
                "curp" => $data["curp"],
                "clave_elector" => $data["clave_elector"],
                "foto_ruta" => $data["foto_ruta"],
                "dom_calle" => $data["dom_calle"],
                "dom_numero" => $data["dom_numero"],
                "dom_cruz1" => $data["dom_cruz1"],
                "dom_cruz2" => $data["dom_cruz2"],
                "dom_colonia" => $data["dom_colonia"],
                "dom_cp" => $data["dom_cp"],
                "idestado" => $data["idestado"],
                "idmunicipio" => $data["idmunicipio"],
                "dom_referencia" => $data["dom_referencia"],
            ];

            $this->db->beginTransaction();

            // 1. Insertar en PERSONAS
            $sqlPersona = "INSERT INTO personas (
                ap_paterno, ap_materno, nombres,
                email, telefono, sexo, fecha_nacimiento,
                curp, clave_elector, foto_ruta,
                dom_calle, dom_numero, dom_cruz1, dom_cruz2,
                dom_colonia, dom_cp, idestado, idmunicipio, dom_referencia,
                idrol, activo, created_at
            ) VALUES (
                :ap_paterno, :ap_materno, :nombres,
                :email, :telefono, :sexo, :fecha_nacimiento,
                :curp, :clave_elector, :foto_ruta,
                :dom_calle, :dom_numero, :dom_cruz1, :dom_cruz2,
                :dom_colonia, :dom_cp, :idestado, :idmunicipio, :dom_referencia,
                3, 1, NOW()
            )";

            $stmtPersona = $this->db->prepare($sqlPersona);
            $stmtPersona->execute($dataPersona);

            $idPersona = (int)$this->db->lastInsertId();

            $this->db->commit();

            return $idPersona;
        } catch (Throwable $e) {
            var_dump($e);
            $this->db->rollBack();
            throw $e;
        }
    }

    public function obtenerPorId(int $idPersona): ?array
    {
        $sql = "SELECT
                    p.idpersona AS idcobratario,
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
                  AND p.idrol = 3
                  AND p.activo = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idPersona]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function actualizarCobratario(int $idPersona, array $data): void
    {
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
                  AND idrol = 3";

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
    }

    public function eliminarCobratario(int $idPersona): void
    {
        $sql = "UPDATE personas
                SET activo = 0
                WHERE idpersona = :idpersona
                  AND idrol = 3";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idpersona' => $idPersona]);
    }
}
