<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class RaptorService
{
    use ImportService, ExportService;

    protected ?string $errorMessage = null;
    protected ?string $successMessage = null;

    public function __construct(public Model $model, public Request $request) {}



    public function create(array $data)
    {
        try {
            DB::beginTransaction();
            $this->beforeStore($data);
            $model = $this->model->create($data);
            $this->afterStore($data, $model);

            // Process any additional data or relationships here

            DB::commit();
            $this->setSuccess('Registro criado com sucesso!');
            return $model;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating {{name}}: ' . $e->getMessage());
            $this->setError($e->getMessage());
            return null;
        }
    }

    public function update($model, array $data)
    {
        try {
            DB::beginTransaction();
            $this->beforeUpdate($model, $data);
            $model->update($data);
            $this->afterUpdate($model, $data);
            // Process any additional data or relationships here

            DB::commit();
            $this->setSuccess('Registro atualizado com sucesso!');
            return $model;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating {{name}}: ' . $e->getMessage());
            $this->setError($e->getMessage());
            return null;
        }
    }

    public function delete($model)
    {
        try {
            DB::beginTransaction();
            $this->beforeDelete($model);
            $model->delete();
            $this->afterDelete($model);
            // Process any additional data or relationships here

            DB::commit();
            $this->setSuccess('Registro excluído com sucesso!');
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting {{name}}: ' . $e->getMessage());
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    protected function afterStore(array $data): void
    {
        // Implement in child classes
    }
    protected function afterUpdate($model, array $data): void
    {
        // Implement in child classes
    }

    protected function afterDelete(): void
    {
        // Implement in child classes
    }

    protected function beforeStore(array $data): void
    {
        // Implement in child classes
    }

    protected function beforeUpdate($model, array $data): void
    {
        // Implement in child classes
    }

    protected function beforeDelete(): void
    {
        // Implement in child classes
    }

    public function getError(): ?string
    {
        return $this->errorMessage;
    }
    public function getSuccess(): ?string
    {
        return $this->successMessage;
    }

    public function setError(string $error): void
    {
        $this->errorMessage = $error;
    }

    public function setSuccess(string $success): void
    {
        $this->successMessage = $success;
    }
}
