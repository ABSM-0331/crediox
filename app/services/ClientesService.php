<?php
class ClientesService
{
    private ClientesRepository $repository;

    public function __construct()
    {
        $this->repository = new ClientesRepository();
    }

    public function obtenerTodos(): array
    {
        return $this->repository->obtenerTodos();
    }

    public function crearCliente(array $data): int
    {
        return $this->repository->crearCliente($data);
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->repository->obtenerPorId($id);
    }

    public function actualizarCliente(int $id, array $data): void
    {
        $this->repository->actualizarCliente($id, $data);
    }

    public function eliminarCliente(int $id): void
    {
        $this->repository->eliminarCliente($id);
    }
}
