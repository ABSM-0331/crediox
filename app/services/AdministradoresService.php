<?php
class AdministradoresService
{
    private AdministradoresRepository $repository;

    public function __construct()
    {
        $this->repository = new AdministradoresRepository();
    }

    public function obtenerTodos(): array
    {
        return $this->repository->obtenerTodos();
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->repository->obtenerPorId($id);
    }

    public function crearAdministrador(array $data): int
    {
        return $this->repository->crearAdministrador($data);
    }

    public function actualizarAdministrador(int $id, array $data): void
    {
        $this->repository->actualizarAdministrador($id, $data);
    }

    public function eliminarAdministrador(int $id): void
    {
        $this->repository->eliminarAdministrador($id);
    }
}
