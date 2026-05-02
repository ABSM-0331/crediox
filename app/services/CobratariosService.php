<?php
class CobratariosService
{
    private CobratariosRepository $repository;

    public function __construct()
    {
        $this->repository = new CobratariosRepository();
    }

    public function obtenerTodos(): array
    {
        return $this->repository->obtenerTodos();
    }

    public function obtenerTodosConEstadisticas(): array
    {
        return $this->repository->obtenerTodosConEstadisticas();
    }

    public function obtenerClientesAsignadosActivosPorCobratario(): array
    {
        return $this->repository->obtenerClientesAsignadosActivosPorCobratario();
    }

    public function reasignarClienteActivo(int $idCliente, int $idCobratarioActual, int $idCobratarioNuevo): int
    {
        return $this->repository->reasignarClienteActivo($idCliente, $idCobratarioActual, $idCobratarioNuevo);
    }

    public function quitarClienteActivo(int $idCliente, int $idCobratarioActual): int
    {
        return $this->repository->quitarClienteActivo($idCliente, $idCobratarioActual);
    }

    public function obtenerCreditosActivosSinCobratario(): array
    {
        return $this->repository->obtenerCreditosActivosSinCobratario();
    }

    public function asignarCreditoSinCobratario(int $idCredito, int $idCobratarioDestino): int
    {
        return $this->repository->asignarCreditoSinCobratario($idCredito, $idCobratarioDestino);
    }

    public function crearCobratario(array $data): void
    {
        $this->repository->crearCobratario($data);
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->repository->obtenerPorId($id);
    }

    public function actualizarCobratario(int $id, array $data): void
    {
        $this->repository->actualizarCobratario($id, $data);
    }

    public function eliminarCobratario(int $id): void
    {
        $this->repository->eliminarCobratario($id);
    }
}
