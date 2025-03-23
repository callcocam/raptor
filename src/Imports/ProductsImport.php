<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Callcocam\Plannerate\Enums\ProductStatus;
use Callcocam\Plannerate\Models\Image;
use Callcocam\Raptor\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image as ImageIntervention;

class ProductsImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Ignorar cabeçalho (primeira linha neste caso)
        $rows = $rows->slice(1);

        // Obter o tenant e um usuário aleatório uma vez
        $tenant = $this->getTenantModel()::query()->first();
        if (!$tenant) {
            Log::error('Tenant not found.');
            return;
        }
        if ($rows->isEmpty()) {
            Log::info('No rows to import.');
            return;
        }
        // Obter um usuário aleatório
        $userModel = $this->getUserModel();
        if (!$userModel) {
            Log::error('User model not found.');
            return;
        }
        $user = $userModel::query()->inRandomOrder()->first();
        if (!$user) {
            Log::error('User not found.');
            return;
        }
        // Obter o modelo de categoria e produto
        $categoryModel = $this->getCategoryModel();
        if (!$categoryModel) {
            Log::error('Category model not found.');
            return;
        }
        $productModel = $this->getProductModel();
        if (!$productModel) {
            Log::error('Product model not found.');
            return;
        }

        // Deletar todos os produtos e categorias existentes
        $productModel::query()->forceDelete();
        $categoryModel::query()->forceDelete();
        // Deletar todas as imagens
        $imageModel = config('raptor.models.image', Image::class);
        if ($imageModel) {
            $imageModel::query()->forceDelete();
        } else {
            Log::error('Image model not found.');
        }

        $user = User::query()->inRandomOrder()->first();

        foreach ($rows as $row) {
            // Validar dados essenciais
            if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                Log::info('Ignoring row due to missing data:', [
                    'ean' => $row[0] ?? null,
                    'name' => $row[1] ?? null,
                    'category_id' => $row[3] ?? null,
                ]);
                continue;
            }
            $slug = Str::slug($row[3]);
            // Criar ou obter a categoria
            $category = $categoryModel::query()
                ->where('slug', $slug)
                ->first();
            if (!$category) {
                $category = $categoryModel::query()->create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'name' => $row[3], // Categoria
                    'slug' => $slug,
                    'status' => 'published',
                ]);
            }
            if ($productModel::query()->where('ean', $row[1])->exists()) {
                Log::info('Ignoring row due to duplicate ean:', [
                    'ean' => $row[1] ?? null,
                    'name' => $row[2] ?? null,
                ]);
                continue;
            }
            // Criar o produto
            $productModel::create([
                'tenant_id'    => $tenant->id,
                'user_id'      => $user->id,
                'category_id'  => $category->id,
                'ean'          => $row[1], // EAN
                'name'         => $row[2], // Nome
                'slug'         => Str::slug($row[2]),
                'height'       => $row[4] ?? null, // Altura
                'width'        => $row[5] ?? null, // Largura
                'depth'        => $row[6] ?? null, // Profundidade
                'created_at'   => now()->subDays(rand(1, 365)),
                'updated_at'   => now()->subDays(rand(1, 365)),
                'status'       => ProductStatus::PUBLISHED->value,
            ]);
        }

        $this->importImages();
        Log::info('Importação de produtos concluída.');
    }

    protected function getCategoryModel()
    {
        return config('raptor.models.category', Category::class);
    }

    protected function getProductModel()
    {
        return config('raptor.models.product', Product::class);
    }

    protected function getUserModel()
    {
        return config('raptor.models.user', User::class);
    }

    protected function getTenantModel()
    {
        return config('raptor.models.tenant', Tenant::class);
    }

    /**
     * Método para importar imagens
     * 
     * @return void
     */
    public function importImages(): void
    {
        // Verifica se o modelo de imagem está definido
        $imageModel = config('raptor.models.image', Image::class);
        if (!class_exists($imageModel)) {
            Log::error('Image model not found.');
            return;
        }
        // Verifica se o modelo de usuário está definido

        $imageModel::query()->forceDelete();
        // Configuração
        $folderPath = storage_path('app/public/images'); // Pasta das imagens
        $disk = 'public'; // Nome do disco configurado no Laravel
        $defaultUserId = User::all()->random()->id; // ID do usuário padrão
        $defaultStatus = 'published'; // Status padrão

        // Verificar se a pasta existe
        if (!is_dir($folderPath)) {
            Log::error('Image folder not found: ' . $folderPath);
            return;
        }

        // Obter todos os arquivos da pasta
        $files = array_filter(scandir($folderPath), function ($file) use ($folderPath) {
            return is_file($folderPath . DIRECTORY_SEPARATOR . $file);
        });
        foreach ($files as $file) {
            $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;

            // Obter informações do arquivo
            $fileInfo = pathinfo($filePath);
            $size = filesize($filePath);
            $mimeType = mime_content_type($filePath);
            $name = $fileInfo['filename'];
            $extension = $fileInfo['extension'];
            $slug = Str::slug($name);
            $altText = "Imagem de {$name}";

            // Usar Intervention Image para obter dimensões
            $image = ImageIntervention::read($filePath);
            $width = $image->width();
            $height = $image->height();

            // Inserir no banco de dados
            $new =    $imageModel::query()->create([
                'user_id' => $defaultUserId,
                'path' => "images/{$file}",
                'name' => $name,
                'slug' => $slug,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => $size,
                'disk' => $disk,
                'width' => $width,
                'height' => $height,
                'alt_text' => $altText,
                'status' => $defaultStatus,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $productModel = $this->getProductModel();
            // Verifica se o produto existe

            if ($product = $productModel::query()->where('ean', $name)->first()) {
                $product->image_id = $new->id;
                $product->save();
            }
        }
    }
}
