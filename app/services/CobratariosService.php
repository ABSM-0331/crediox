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
