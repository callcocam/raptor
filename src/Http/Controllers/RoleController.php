<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Http\Controllers;

use Callcocam\Raptor\Core\Support\Form\Fields\CheckboxListInput;
use Callcocam\Raptor\Core\Support\Form\Fields\RadioInput;
use Callcocam\Raptor\Core\Support\Form\Fields\SwitchInput;
use Callcocam\Raptor\Core\Support\Form\Fields\TextAreaInput;
use Callcocam\Raptor\Core\Support\Form\Fields\TextInput;
use Callcocam\Raptor\Http\Resources\RoleResource;
use Callcocam\Raptor\Models\Role;
use Callcocam\Raptor\Core\Support\Table\Table;
use Callcocam\Raptor\Core\Support\Form\Form;
use Callcocam\Raptor\Core\Support\Form\Section;
use Callcocam\Raptor\Core\Support\Table\Actions\Bulk\DeleteBulkAction;
use Callcocam\Raptor\Core\Support\Table\Actions\EditAction;
use Callcocam\Raptor\Support\Core\Table\Actions\DeleteAction;
use Callcocam\Raptor\Core\Support\Table\Columns\TextColumn;
use Callcocam\Raptor\Core\Support\Table\Filters\SelectFilter;
use Callcocam\Raptor\Http\Requests\Role\UpdateRequest;
use Callcocam\Raptor\Http\Requests\Role\StoreRequest;
use Callcocam\Raptor\Services\RoleService;
use Closure;

class RoleController extends RaptorController
{
    protected ?string $model = Role::class;
    protected ?string $resource = RoleResource::class;
    protected int | string | Closure | null $navigationSort = 2;
    protected string | Closure | null $navigationGroup = 'Configurações';

    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }

    protected function form(Form $form): Form
    {
        return $form
            ->appendLoad(['access'])
            ->sections([
                Section::make('Dados')
                    ->columns(1)
                    ->label('Dados')
                    ->fields([
                        TextInput::make('name')->label('Nome')->required(),
                        SwitchInput::make('special')->label('Especial'),
                        RadioInput::make('status')->label('Situação')->options([
                            'draft' => 'Rascunho',
                            'published' => 'Publicado',
                        ])->required(),
                        TextAreaInput::make('description')->label('Descrição'),
                    ]),
                Section::make('Permissões')
                    ->columns(1)
                    ->label('Permissões')
                    ->fields([
                        CheckboxListInput::make('access')->label('Permissões')->options($this->service->getPermissionsOptions()),
                    ]),
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('status')->sortable()->searchable(),
                TextColumn::make('created_at')->format(fn($value) => $value->diffForHumans()),
                TextColumn::make('updated_at')->format(fn($value) => $value->diffForHumans()),
            ])
            ->actions([
                EditAction::make()->route('roles.edit'),
                // DeleteAction::make()->route('roles.destroy'),
            ])
            ->filters([
                SelectFilter::make('status', 'Situação')
                    ->options([
                        'draft' => 'Rascunho',
                        'published' => 'Publicado',
                    ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make('Excluir Selecionados')
                    ->requireConfirmation(true)
                    ->url(fn($model) => route('roles.destroy', $model->id)),
            ]);
    }


    public function update(UpdateRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $model = $this->getModel()::findOrFail($id);
            $this->service->update($model, $validated);
            return redirect()->route($this->routePrefix('index'))->with('success', 'Registro atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }


    public function store(StoreRequest $request)
    {
        try {
            $validated = $request->validated();
            $this->service->create($validated);
            return redirect()->route($this->routePrefix('index'))->with('success', 'Registro criado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    protected function defaults(): array
    {
        return array_merge(parent::defaults(), [
            'access' => [],
        ]);
    }
}
