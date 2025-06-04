<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Http\Controllers;

use Callcocam\Raptor\Enums\UserStatus;
use Callcocam\Raptor\Models\Tenant;
use Callcocam\Raptor\Models\Auth\User;
use Callcocam\Raptor\Core\Support\Column;
use Callcocam\Raptor\Core\Support\Field;
use Callcocam\Raptor\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends AbstractController
{
    protected ?string $model = User::class;

    public function getSidebarMenuOrder(): int
    {
        return 20;
    }

    public function getSidebarMenuIconName(): string
    {
        return 'Users';
    }

    public function getSidebarMenuTitle(): string
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
        return [
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', $isUpdate ? Rule::unique('users')->ignore($userId) : Rule::unique('users')],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', Password::defaults(), 'confirmed'],
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'avatar' => ['nullable'],
            'status' => ['required', Rule::in(array_column(UserStatus::cases(), 'value'))],
        ];
    }

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
        
        if ($user && $roleIds) {
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
        
        if ($roleIds) {
            $user->roles()->sync($roleIds);
        } else {
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
                Storage::disk(config('filesystems.default'))->delete($avatarPath);
            }
            return redirect()->route($this->getRouteNameBase() . '.index')
                ->with('success', Str::ucfirst(Str::singular($this->getResourceName())) . ' excluído(a) com sucesso.');
        } else {
            return redirect()->route($this->getRouteNameBase() . '.index')
                ->with('error', 'Erro ao excluir ' . Str::singular($this->getResourceName()) . '.');
        }
    }
} 