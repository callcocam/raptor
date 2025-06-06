<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Models\Tenant;
use App\Models\User;
use Callcocam\LaraGatekeeper\Core\Support\Column;
use Callcocam\LaraGatekeeper\Core\Support\Field;
use Callcocam\LaraGatekeeper\Http\Controllers\AbstractController;
use Callcocam\LaraGatekeeper\Models\Role;
use Callcocam\LaraGatekeeper\Traits\ManagesSidebarMenu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;

class UserController extends AbstractController
{

    protected ?string $model = User::class;

    public function getSidebarMenuOrder(): int
    {
        return 20;
    }

    

    public function getSidebarMenuIconName(): string
    {
        return __('Usuários');
    }

    protected function getFields(?Model $model = null): array
    {
        $isUpdate = $model && $model->exists;

        return [
            Field::make('name', 'Nome Completo')
                ->type('text')
                ->required()
                ->colSpan(6),

            Field::make('email', 'E-mail')
                ->type('email')
                ->required()
                ->colSpan(6),

            Field::make('password', 'Senha')
                ->type('password')
                ->required(!$isUpdate)
                ->placeholder($isUpdate ? 'Deixe em branco para não alterar' : '')
                ->colSpan(6),

            Field::make('password_confirmation', 'Confirmar Senha')
                ->type('password')
                ->required(!$isUpdate)
                ->colSpan(6),

            Field::make('avatar', $isUpdate ? 'Alterar Avatar' : 'Avatar')
                ->type('filepond')
                ->accept('image/*')
                ->colSpan(12),

            Field::make('roles', 'Papéis')
                ->type('checkboxList')
                ->relationship('roles', 'name', 'id')
                ->options(Role::pluck('name', 'id')->toArray())
                ->gridCols(3)
                ->colSpan(12),

            Field::make('status', 'Status')
                ->type('select')
                ->options(UserStatus::getOptions())
                ->required()
                ->colSpan(12),
        ];
    }

    protected function getTableColumns(): array
    {
        $columns = [
            Column::make('Avatar')
                ->id('avatar')
                ->accessorKey(null)
                ->hideable()
                ->html()
                ->cell(function (User $row) {
                    $url = $row->avatar ? Storage::disk(config('filesystems.default'))->url($row->avatar) : null;
                    return $url ? '<img src="' . $url . '" alt="Avatar" class="h-8 w-8 rounded-full object-cover">' : '-';
                }),

            Column::make('Nome', 'name')->sortable(),

            Column::make('E-mail', 'email')->sortable(),

            Column::make('Criado em', 'created_at')
                ->sortable()
                ->formatter('formatDate')
                ->options('dd/MM/yyyy HH:mm'),

            Column::make('Status', 'status')
                ->sortable()
                ->formatter('renderBadge')
                ->options(UserStatus::variantOptions()),

            Column::actions(),
        ];

        return $columns;
    }

    protected function getSearchableColumns(): array
    {
        return ['name', 'email'];
    }

    protected function getFilters(): array
    {
        return [
            [
                'column' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => UserStatus::options(),
            ]
        ];
    }

    protected function getValidationRules(bool $isUpdate = false, ?Model $model = null): array
    {
        $userId = $model?->id;
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', $isUpdate ? Rule::unique('users')->ignore($userId) : Rule::unique('users')],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', Password::defaults(), 'confirmed'],
            'roles' => 'nullable|array',
            // 'roles.*' => 'exists:roles,id',
            'avatar' => ['nullable'],
            'status' => ['required', Rule::in(array_column(UserStatus::cases(), 'value'))],
        ];

        return $rules;
    }

    /**
     * Define os relacionamentos que devem ser carregados (eager loaded)
     * na listagem principal (método index).
     *
     * @return array
     */
    protected function getWithRelations(): array
    {
        return ['roles'];
    }
 

    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate($this->getValidationRules());
        $roleIds = $validatedData['roles'] ?? [];
        $avatarTempPath = $validatedData['avatar'] ?? null;

        unset($validatedData['roles'], $validatedData['avatar']);

        if ($avatarTempPath) {
            $validatedData['avatar'] = $this->moveTemporaryFile($avatarTempPath, 'avatars');
        }

        $validatedData['password'] = bcrypt($validatedData['password']);
        $user = $this->model::create($validatedData);
        if ($user) {
            $user->roles()->sync($roleIds);
        }

        return redirect()->route($this->getRouteNameBase() . '.index')
            ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' criado(a) com sucesso.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        
        $user = $this->model::findOrFail($id);
        $validatedData = $request->validate($this->getValidationRules(true, $user));
        $roleIds = $validatedData['roles'] ?? [];
        $avatarTempPath = $validatedData['avatar'] ?? null;

        unset($validatedData['roles'], $validatedData['avatar']);

        if ($request->filled('avatar')) {
            $newPath = null;
            if ($avatarTempPath) {
                $newPath = $this->moveTemporaryFile($avatarTempPath, 'avatars');
            }

            if ($newPath !== $user->avatar) {
                $oldAvatarPath = $user->avatar;
                $validatedData['avatar'] = $newPath;
                if ($oldAvatarPath && ($newPath || is_null($avatarTempPath))) {
                    Storage::disk(config('filesystems.default'))->delete($oldAvatarPath);
                }
            }
        }

        if (!empty($validatedData['password'])) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        } else {
            unset($validatedData['password']);
        } 
        $user->update($validatedData); 
        if($roleIds) {
            $user->roles()->sync($roleIds);
        }else{
            $user->roles()->detach();
        }

        return redirect()->route($this->getRouteNameBase() . '.index')
            ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' atualizado(a) com sucesso.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $user = $this->model::findOrFail($id);
        $avatarPath = $user->avatar;

        if ($user->delete()) {
            if ($avatarPath) {
                Storage::disk(config('filesystems.default',))->delete($avatarPath);
            }
            return redirect()->route($this->getRouteNameBase() . '.index')
                ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' excluído(a) com sucesso.');
        } else {
            return redirect()->route($this->getRouteNameBase() . '.index')
                ->with('error', 'Erro ao excluir ' . Str::singular($this->getResourceName()) . '.');
        }
    }


    // protected function getInitialValuesForEdit(Model $modelInstance): array
    // {
    //     $values = $modelInstance->toArray();
    //     if ($modelInstance->relationLoaded('roles')) {
    //         $values['roles'] = $modelInstance->roles->pluck('id')->toArray();
    //     }
    //     unset($values['password']);

    //     // // Adiciona a URL completa do avatar se existir
    //     if (!empty($modelInstance->avatar)) {
    //         $values['avatar'] = Storage::disk(config('filesystems.default'))->url($modelInstance->avatar);
    //     } else {
    //         $values['avatar'] = null; // Garante que a chave exista mesmo se vazia
    //     }

    //     return $values;
    // }
}
