<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Http\Controllers;

use Callcocam\Raptor\Core\Support\Form\Fields\RadioInput;
use Callcocam\Raptor\Http\Resources\AboutResource;
use Callcocam\Raptor\Models\About;
use Callcocam\Raptor\Core\Support\Table\Table;
use Callcocam\Raptor\Core\Support\Form\Form;

use Callcocam\Raptor\Core\Support\Table\Actions\Bulk\DeleteBulkAction;
use Callcocam\Raptor\Core\Support\Table\Actions\EditAction; 
use Callcocam\Raptor\Core\Support\Table\Columns\TextColumn;
use Callcocam\Raptor\Core\Support\Table\Filters\SelectFilter;
use Callcocam\Raptor\Core\Support\Form\Fields\TextAreaInput; 
use Callcocam\Raptor\Core\Support\Form\Fields\TextInput;
use Callcocam\Raptor\Core\Support\Form\Section;
use Callcocam\Raptor\Http\Requests\About\UpdateRequest;
use Callcocam\Raptor\Http\Requests\About\StoreRequest;
use Callcocam\Raptor\Services\AboutService;
use Closure;

class AboutController extends RaptorController
{
    protected ?string $model = About::class;
    protected ?string $resource = AboutResource::class;
    protected string | Closure | null $slug = 'abouts';
    protected int | string | Closure | null $navigationSort = 12;

    public function __construct(AboutService $service)
    {
        $this->service = $service;
    }

    protected function form(Form $form): Form
    {
        return $form
            ->sections([
                Section::make('Dados')
                    ->label('Dados')
                    ->fields([
                        TextInput::make('name')->label('Nome')->required()->columnSpanFull(),
                        RadioInput::make('status')->label('Situação')
                            ->options([
                                'draft' => 'Rascunho',
                                'published' => 'Publicado',
                            ])->required()->columnSpanFull(),
                        TextAreaInput::make('description')->label('Descrição')->columnSpanFull(),
                    ]),
            ]);
    }

    protected function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('status')->sortable()->searchable(),
                TextColumn::make('created_at')->format(fn($value) => $value->diffForHumans()),
                TextColumn::make('updated_at')->format(fn($value) => $value->diffForHumans()),
            ])
            ->actions([
                EditAction::make()->route('abouts.edit'),
                //DeleteAction::make()->route('abouts.destroy'),
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
                    ->url(fn($model) => route('abouts.destroy', $model->id)),
            ]);
    }


    public function update(UpdateRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $model = $this->getModel()::findOrFail($id);
            if ($this->service->update($model, $validated)) {
                return redirect()->route($this->routePrefix('index'))->with('success', 'Registro atualizado com sucesso!');
            }
            return redirect()->back()->withErrors('Erro ao atualizar o registro')->with('error', $this->service->getError());
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }


    public function store(StoreRequest $request)
    {
        try {
            $validated = $request->validated();
            if ($this->service->create(array_merge($validated, ['user_id' => auth()->user()->id]))) {
                return redirect()->route($this->routePrefix('index'))->with('success', 'Registro criado com sucesso!');
            }
            return redirect()->back()->withErrors('Erro ao criar o registro')->with('error', $this->service->getError());
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
