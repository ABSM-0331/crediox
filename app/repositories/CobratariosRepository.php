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
                    WHERE c.estado = 'activo'
                      AND c.saldo_pendiente > 0
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

    public function obtenerClientesAsignadosActivosPorCobratario(): array
    {
        $sql = "SELECT
                    c.idcobratario,
                    c.idcliente,
                    CONCAT(cli.ap_paterno, ' ', cli.ap_materno, ' ', cli.nombres) AS cliente,
                    cli.telefono,
                    cli.email,
                    COUNT(c.idcredito) AS creditos_activos,
                    COALESCE(SUM(c.saldo_pendiente), 0) AS saldo_pendiente
                FROM creditos c
                INNER JOIN personas cli ON cli.idpersona = c.idcliente AND cli.activo = 1
                INNER JOIN personas cob ON cob.idpersona = c.idcobratario AND cob.idrol = 3 AND cob.activo = 1
                WHERE c.idcobratario IS NOT NULL
                  AND c.estado = 'activo'
                  AND c.saldo_pendiente > 0
                GROUP BY c.idcobratario, c.idcliente, cli.ap_paterno, cli.ap_materno, cli.nombres, cli.telefono, cli.email
                ORDER BY cob.ap_paterno, cob.ap_materno, cob.nombres, cli.ap_paterno, cli.ap_materno, cli.nombres";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function reasignarClienteActivo(int $idCliente, int $idCobratarioActual, int $idCobratarioNuevo): int
    {
        if ($idCobratarioActual === $idCobratarioNuevo) {
            throw new Exception('El cobratario destino debe ser diferente al actual');
        }

        $sqlValida = "SELECT idpersona
                      FROM personas
                      WHERE idpersona = :idcobratario
                        AND idrol = 3
                        AND activo = 1
                      LIMIT 1";
        $stmtValida = $this->db->prepare($sqlValida);
        $stmtValida->execute(['idcobratario' => $idCobratarioNuevo]);
        if (!$stmtValida->fetch()) {
            throw new Exception('Cobratario destino no válido');
        }

        $sql = "UPDATE creditos
                SET idcobratario = :idcobratario_nuevo
                WHERE idcliente = :idcliente
                  AND idcobratario = :idcobratario_actual
                  AND estado = 'activo'
                  AND saldo_pendiente > 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'idcobratario_nuevo' => $idCobratarioNuevo,
            'idcliente' => $idCliente,
            'idcobratario_actual' => $idCobratarioActual,
        ]);

        return $stmt->rowCount();
    }

    public function quitarClienteActivo(int $idCliente, int $idCobratarioActual): int
    {
        $sql = "UPDATE creditos
                SET idcobratario = NULL
                WHERE idcliente = :idcliente
                  AND idcobratario = :idcobratario_actual
                  AND estado = 'activo'
                  AND saldo_pendiente > 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'idcliente' => $idCliente,
            'idcobratario_actual' => $idCobratarioActual,
        ]);

        return $stmt->rowCount();
    }

    public function obtenerCreditosActivosSinCobratario(): array
    {
        $sql = "SELECT
                    c.idcredito,
                    c.idcliente,
                    c.tipo,
                    c.monto,
                    c.saldo_pendiente,
                    c.fecha_inicio,
                    CONCAT(cli.ap_paterno, ' ', cli.ap_materno, ' ', cli.nombres) AS cliente,
                    cli.telefono,
                    cli.email
                FROM creditos c
                INNER JOIN personas cli ON cli.idpersona = c.idcliente AND cli.activo = 1
                WHERE c.idcobratario IS NULL
                  AND c.estado = 'activo'
                  AND c.saldo_pendiente > 0
                ORDER BY c.idcredito DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function asignarCreditoSinCobratario(int $idCredito, int $idCobratarioDestino): int
    {
        $sqlValida = "SELECT idpersona
                      FROM personas
                      WHERE idpersona = :idcobratario
                        AND idrol = 3
                        AND activo = 1
                      LIMIT 1";
        $stmtValida = $this->db->prepare($sqlValida);
        $stmtValida->execute(['idcobratario' => $idCobratarioDestino]);
        if (!$stmtValida->fetch()) {
            throw new Exception('Cobratario destino no válido');
        }

        $sql = "UPDATE creditos
                SET idcobratario = :idcobratario
                WHERE idcredito = :idcredito
                  AND idcobratario IS NULL
                  AND estado = 'activo'
                  AND saldo_pendiente > 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'idcobratario' => $idCobratarioDestino,
            'idcredito' => $idCredito,
        ]);

        return $stmt->rowCount();
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
